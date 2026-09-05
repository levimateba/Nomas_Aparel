<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeManage();

        $query = Employee::query()->with('user')->latest();

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('other_names', 'like', $term)
                    ->orWhere('employee_number', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('job_title', 'like', $term)
                    ->orWhere('department', 'like', $term)
                    ->orWhere('national_id', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'users') {
                $query->whereNotNull('user_id');
            } elseif ($request->status === 'no_login') {
                $query->whereNull('user_id');
            }
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $employees = $query->paginate(12)->withQueryString();

        $departments = Employee::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('admin.employees.index', [
            'employees' => $employees,
            'departments' => $departments,
            'stats' => [
                'total' => Employee::count(),
                'active' => Employee::where('is_active', true)->count(),
                'inactive' => Employee::where('is_active', false)->count(),
                'users' => Employee::whereNotNull('user_id')->count(),
            ],
        ]);
    }

    public function create()
    {
        $this->authorizeManage();

        return view('admin.employees.create', [
            'employee' => new Employee([
                'employee_number' => Employee::nextEmployeeNumber(),
                'is_active' => true,
                'employment_type' => 'full_time',
                'hire_date' => now()->toDateString(),
            ]),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $this->validated($request);
        $data['photo'] = $this->storePhoto($request);
        $data['is_active'] = $request->boolean('is_active', true);

        $credentials = null;

        $employee = DB::transaction(function () use ($request, $data, &$credentials) {
            $employee = Employee::create($data);

            if ($request->boolean('create_as_user')) {
                $credentials = $this->provisionUser($employee, $request);
            }

            return $employee;
        });

        $redirect = redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee created.');

        if ($credentials) {
            if (! empty($credentials['linked'])) {
                $redirect->with('success', 'Employee created and linked to the existing login account ('.$credentials['email'].').');
            } else {
                $redirect->with('generated_credentials', $credentials);
            }
        }

        return $redirect;
    }

    public function show(Employee $employee)
    {
        $this->authorizeManage();

        return view('admin.employees.show', [
            'employee' => $employee->load('user.role', 'user.roles'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function edit(Employee $employee)
    {
        $this->authorizeManage();

        return view('admin.employees.edit', [
            'employee' => $employee->load('user'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeManage();

        $data = $this->validated($request, $employee);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_photo') && $employee->photoStoragePath()) {
            try {
                Storage::disk('public')->delete($employee->photoStoragePath());
            } catch (\Throwable) {
            }
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($employee->photoStoragePath()) {
                try {
                    Storage::disk('public')->delete($employee->photoStoragePath());
                } catch (\Throwable) {
                }
            }
            $data['photo'] = $this->storePhoto($request);
        } else {
            unset($data['photo']);
        }

        $credentials = null;

        DB::transaction(function () use ($request, $employee, $data, &$credentials) {
            $employee->update($data);

            if ($request->boolean('create_as_user') && ! $employee->user_id) {
                $credentials = $this->provisionUser($employee->fresh(), $request);
            }

            if ($employee->user_id) {
                $user = $employee->user;
                if ($user) {
                    $user->update([
                        'name' => $employee->fullName(),
                        'email' => $employee->email ?: $user->email,
                        'phone' => $employee->phone,
                        'job_title' => $employee->job_title,
                        'employee_code' => $employee->employee_number,
                    ]);
                }
            }
        });

        $redirect = redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee updated.');

        if ($credentials) {
            if (! empty($credentials['linked'])) {
                $redirect->with('success', 'Employee updated and linked to the existing login account ('.$credentials['email'].').');
            } else {
                $redirect->with('generated_credentials', $credentials);
            }
        }

        return $redirect;
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeManage();

        if ($employee->photoStoragePath()) {
            try {
                Storage::disk('public')->delete($employee->photoStoragePath());
            } catch (\Throwable) {
            }
        }

        // Keep the login account; only unlink HR record
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee removed.');
    }

    public function createLogin(Request $request, Employee $employee)
    {
        $this->authorizeManage();

        abort_if($employee->user_id, 422, 'This employee already has a login.');

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'email' => 'nullable|email|max:255',
        ]);

        if (! $employee->email && ! $request->filled('email')) {
            return back()->withErrors(['email' => 'Set an email on the employee (or provide one) before creating a login.'])->withInput();
        }

        if ($request->filled('email') && $request->email !== $employee->email) {
            $employee->update(['email' => $request->email]);
        }

        $credentials = $this->provisionUser($employee->fresh(), $request);

        $redirect = redirect()->route('admin.employees.show', $employee);

        if (! empty($credentials['linked'])) {
            return $redirect->with('success', 'Existing login account linked ('.$credentials['email'].').');
        }

        return $redirect
            ->with('success', 'Login account created.')
            ->with('generated_credentials', $credentials);
    }

    public function resetLogin(Employee $employee)
    {
        $this->authorizeManage();

        abort_unless($employee->user_id && $employee->user, 404);

        $password = $this->generatePassword();
        $employee->user->update(['password' => $password]);

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Password regenerated.')
            ->with('generated_credentials', [
                'name' => $employee->fullName(),
                'email' => $employee->user->email,
                'password' => $password,
                'employee_number' => $employee->employee_number,
            ]);
    }

    public function printForm(Employee $employee)
    {
        $this->authorizeManage();

        return view('admin.employees.print-form', [
            'employee' => $employee->load('user.role', 'user.roles'),
            'settings' => \App\Models\Setting::get_settings(),
        ]);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->hasPermission('manage_employees') || auth()->user()?->isFullAdmin(), 403);
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'employee_number' => [
                'required', 'string', 'max:40',
                Rule::unique('employees', 'employee_number')->ignore($employee?->id),
            ],
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'other_names' => 'nullable|string|max:120',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_photo' => 'nullable|boolean',
            'gender' => ['nullable', Rule::in(array_keys(Employee::GENDERS))],
            'date_of_birth' => 'nullable|date|before:today',
            'national_id' => 'nullable|string|max:80',
            'kra_pin' => 'nullable|string|max:80',
            'nhif_number' => 'nullable|string|max:80',
            'nssf_number' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:80',
            'alt_phone' => 'nullable|string|max:80',
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('employees', 'email')->ignore($employee?->id),
            ],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'county' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:40',
            'department' => 'nullable|string|max:120',
            'job_title' => 'nullable|string|max:120',
            'employment_type' => ['nullable', Rule::in(array_keys(Employee::EMPLOYMENT_TYPES))],
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date|after_or_equal:hire_date',
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:120',
            'bank_account' => 'nullable|string|max:80',
            'bank_branch' => 'nullable|string|max:120',
            'emergency_contact_name' => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:80',
            'emergency_contact_relation' => 'nullable|string|max:80',
            'notes' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
            'create_as_user' => 'nullable|boolean',
            'role_id' => 'nullable|required_if:create_as_user,1|exists:roles,id',
        ]);
    }

    private function storePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('employees', 'public');
    }

    /**
     * @return array{name:string,email:string,password:?string,employee_number:string,linked?:bool}
     */
    private function provisionUser(Employee $employee, Request $request): array
    {
        $email = $employee->email ?: $request->input('email');
        if (! $email) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Email is required to create a login.',
            ]);
        }

        $existing = User::query()->where('email', $email)->first();
        $roleId = (int) $request->input('role_id');

        if ($existing) {
            $linkedElsewhere = Employee::query()
                ->where('user_id', $existing->id)
                ->when($employee->id, fn ($q) => $q->where('id', '!=', $employee->id))
                ->exists();

            if ($linkedElsewhere) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'That email is already linked to another employee. Use a different email or unlink the other account first.',
                ]);
            }

            if ($roleId) {
                $existing->update([
                    'name' => $employee->fullName(),
                    'phone' => $employee->phone ?: $existing->phone,
                    'job_title' => $employee->job_title ?: $existing->job_title,
                    'employee_code' => $employee->employee_number,
                    'role_id' => $roleId,
                ]);
                $existing->applyRoleIds([$roleId]);
            } else {
                $existing->update([
                    'name' => $employee->fullName(),
                    'phone' => $employee->phone ?: $existing->phone,
                    'job_title' => $employee->job_title ?: $existing->job_title,
                    'employee_code' => $employee->employee_number,
                ]);
            }

            if (! $employee->email) {
                $employee->email = $email;
            }
            $employee->user_id = $existing->id;
            $employee->save();

            return [
                'name' => $existing->name,
                'email' => $existing->email,
                'password' => null,
                'employee_number' => $employee->employee_number,
                'linked' => true,
            ];
        }

        $password = $this->generatePassword();

        $user = User::create([
            'name' => $employee->fullName(),
            'email' => $email,
            'password' => $password,
            'phone' => $employee->phone,
            'job_title' => $employee->job_title,
            'employee_code' => $employee->employee_number,
            'role_id' => $roleId ?: null,
            'is_admin' => false,
        ]);

        if ($roleId) {
            $user->applyRoleIds([$roleId]);
        }

        if (! $employee->email) {
            $employee->email = $email;
        }
        $employee->user_id = $user->id;
        $employee->save();

        return [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'employee_number' => $employee->employee_number,
            'linked' => false,
        ];
    }

    private function generatePassword(): string
    {
        return Str::password(10, symbols: false);
    }
}
