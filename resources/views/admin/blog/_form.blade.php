<div class="blog-form-fields">
    <div class="blog-row blog-row-two">
        <div class="blog-field">
            <label for="blog-title">Title</label>
            <input id="blog-title" class="blog-input @error('title') is-invalid @enderror" type="text" name="title" value="{{ old('title', $post?->title ?? '') }}" required maxlength="255" placeholder="Write a clear post title">
            @error('title')
                <small class="blog-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="blog-field">
            <label for="blog-category">Category</label>
            <select id="blog-category" class="blog-input @error('category') is-invalid @enderror" name="category">
                <option value="">Select category</option>
                @foreach($blogCategories as $category)
                    <option value="{{ $category }}" {{ old('category', $post?->category ?? '') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
            @error('category')
                <small class="blog-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="blog-row blog-row-two">
        <div class="blog-field">
            <label for="blog-author">Author</label>
            <input id="blog-author" class="blog-input @error('author') is-invalid @enderror" type="text" name="author" value="{{ old('author', $post?->author ?? auth()->user()?->name) }}" maxlength="100">
            @error('author')
                <small class="blog-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="blog-field">
            <label for="blog-published-at">Published Date</label>
            <input id="blog-published-at" class="blog-input @error('published_at') is-invalid @enderror" type="datetime-local" name="published_at" value="{{ old('published_at', !empty($post?->published_at) ? $post?->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
            @error('published_at')
                <small class="blog-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="blog-field">
        <label for="blog-excerpt">Excerpt (Short Summary)</label>
        <textarea id="blog-excerpt" class="blog-input @error('excerpt') is-invalid @enderror" name="excerpt" rows="3" maxlength="500" placeholder="Brief summary for blog cards">{{ old('excerpt', $post?->excerpt ?? '') }}</textarea>
        <small class="blog-help">Keep this concise so cards and previews remain readable.</small>
        @error('excerpt')
            <small class="blog-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="blog-field">
        <label for="blog-content">Content</label>
        <textarea id="blog-content" class="blog-input @error('content') is-invalid @enderror" name="content" rows="10" required placeholder="Write the full blog post here...">{{ old('content', $post?->content ?? '') }}</textarea>
        @error('content')
            <small class="blog-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="blog-row blog-row-two">
        <div class="blog-field">
            <label for="blog-image">Featured Image</label>
            <input id="blog-image" class="blog-input @error('image') is-invalid @enderror" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
            <small class="blog-help">Max file size: 2MB (JPEG, PNG, GIF).</small>
            @error('image')
                <small class="blog-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="blog-field">
            <label class="blog-switch" for="blog-published">
                <input id="blog-published" type="checkbox" name="published" value="1" {{ old('published', $post?->published ?? true) ? 'checked' : '' }}>
                <span>Publish now</span>
            </label>
            <small class="blog-help">Uncheck to save as draft.</small>
        </div>
    </div>
</div>
