<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Портфолио 3D-печати</title>
</head>
<body>
    <h1>Моё портфолио</h1>

    @if ($items->isEmpty())
        <p>Пока нет работ.</p>
    @else
        <ul>
            @foreach ($items as $item)
                <li style="margin-bottom: 20px;">
                    <h2>{{ $item->title }}</h2>
                    <p>{{ $item->description }}</p>

                    @if ($item->images->count())
    <img
        src="{{ asset('storage/' . $item->images->first()->path) }}"
        alt="{{ $item->title }}"
        style="max-width: 320px; height: auto; display:block; margin-top: 8px;"
    >
@endif

                </li>
            @endforeach
        </ul>
    @endif
</body>
</html>
