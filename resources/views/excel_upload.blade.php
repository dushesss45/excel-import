<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка Excel</title>
</head>
<body>
<h1>Загрузка Excel</h1>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div style="color:green;">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('excel.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="file">Выберите Excel (.xlsx):</label>
        <input type="file" name="file" id="file" accept=".xlsx" required>
    </div>
    <button type="submit">Загрузить</button>
</form>
</body>
</html>
