<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Договор № {{ $contract->number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        .signatures {
            margin-top: 40px;
        }
        .signatures td {
            border: none;
            vertical-align: top;
            width: 50%;
        }
    </style>
</head>
<body>

<h1>ДОГОВОР № {{ $contract->number }}</h1>

<p>г. Москва &nbsp;&nbsp;&nbsp; {{ now()->format('d.m.Y') }}</p>

<p><strong>{{ $customerName }}</strong>, именуемое в дальнейшем «Заказчик», в лице {{ $customerPosition }} {{ $customerContact }}, с одной стороны, и Исполнитель, с другой стороны, заключили настоящий Договор о нижеследующем:</p>

<h2>1. ПРЕДМЕТ ДОГОВОРА</h2>

<p>1.1. Исполнитель обязуется оказать услуги по обучению по программе «{{ $program->name }}».</p>
<p>1.2. Стоимость обучения за одного обучающегося: <strong>{{ number_format($program->price, 2, ',', ' ') }} руб.</strong></p>
<p>1.3. Количество обучающихся: <strong>{{ $students->count() }}</strong>.</p>
<p>1.4. Общая стоимость: <strong>{{ number_format($contract->total_amount, 2, ',', ' ') }} руб.</strong></p>

<h2>2. ПЕРЕЧЕНЬ ОБУЧАЮЩИХСЯ</h2>

<table>
    <thead>
        <tr>
            <th>№</th>
            <th>ФИО</th>
            <th>Должность</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->full_name }}</td>
                <td>{{ $student->position ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>3. СРОКИ ОКАЗАНИЯ УСЛУГ</h2>

<p>3.1. Дата начала обучения: <strong>{{ $group->start_date?->format('d.m.Y') ?? '—' }}</strong>.</p>
<p>3.2. Дата окончания обучения: <strong>{{ $group->end_date?->format('d.m.Y') ?? '—' }}</strong>.</p>

<h2>4. ПРОЧИЕ УСЛОВИЯ</h2>

<p>4.1. Договор вступает в силу с момента подписания.</p>

<table class="signatures">
    <tr>
        <td>
            <strong>ЗАКАЗЧИК:</strong><br>
            {{ $customerName }}<br><br>
            ___________________
        </td>
        <td>
            <strong>ИСПОЛНИТЕЛЬ:</strong><br>
            ___________________<br><br>
            ___________________
        </td>
    </tr>
</table>

</body>
</html>