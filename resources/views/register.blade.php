<!-- resources/views/register.blade.php -->
@if (session('error'))
    <div style="color: red;">
        {{ session('error') }}
    </div>
@endif

<form id="addUserForm" action="{{ url('/add-user') }}" method="POST">
    @csrf
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="{{ old('username') }}" required>
    @error('username')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
    @error('name')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
    @error('email')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    @error('password')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <label for="preference">Trip Preference:</label>
    <select id="preference" name="preference" required>
        <option value="indoor" {{ old('preference') == 'indoor' ? 'selected' : '' }}>Indoor</option>
        <option value="outdoor" {{ old('preference') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
    </select>
    @error('preference')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <label for="keywords">Choose Keywords that define you:</label><br>
    <div id="keywords-container">
        <!-- Keywords will load dynamically -->
    </div>
    @error('keywords')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <label for="types">Select Places to Explore:</label><br>
    <div id="types-container">
        <!-- Types will load dynamically -->
    </div>
    @error('types')
        <span style="color:red;">{{ $message }}</span>
    @enderror
    <br><br>

    <button type="submit">Submit</button>
</form>

<div>
    <p>Already have an account? <a href="{{ route('login.user') }}">Login here</a></p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const keywordsContainer = document.getElementById('keywords-container');
        const typesContainer = document.getElementById('types-container');

        // Fetch keywords dynamically
        fetch('/get-keywords')
            .then(response => response.json())
            .then(keywords => {
                keywords.forEach(keyword => {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'keywords[]';
                    checkbox.value = keyword;
                    checkbox.id = keyword;

                    const label = document.createElement('label');
                    label.htmlFor = keyword;
                    label.textContent = keyword.charAt(0).toUpperCase() + keyword.slice(1);

                    keywordsContainer.appendChild(checkbox);
                    keywordsContainer.appendChild(label);
                    keywordsContainer.appendChild(document.createElement('br'));
                });
            });

        // Fetch types dynamically
        fetch('/get-types')
            .then(response => response.json())
            .then(types => {
                types.forEach(type => {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'types[]';
                    checkbox.value = type;
                    checkbox.id = type;

                    const label = document.createElement('label');
                    label.htmlFor = type;
                    label.textContent = type.charAt(0).toUpperCase() + type.slice(1);

                    typesContainer.appendChild(checkbox);
                    typesContainer.appendChild(label);
                    typesContainer.appendChild(document.createElement('br'));
                });
            });
    });
</script>
