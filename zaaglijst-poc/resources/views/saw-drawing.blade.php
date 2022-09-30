<html>
<head>
    <title>Zaaglijst</title>
    <style>
        @page {
            size: 102mm 150mm;
            margin: 0;
            padding: 0;
            font-family: Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
        }

        .page-break {
            page-break-after: always;
        }

        table, th, td {
            border:1px solid black;
        }
    </style>

</head>
<body>
<div style="margin-left: 10px">
    <h1 style="font-size: 20px;">Hoeveelheid rails: {{ $quantity_rails }}</h1>
    <h1 style="font-size: 20px;">Zaagverlies: {{ $saw_loss }}%</h1>
    <table>

        <tr>
            <th>
                Lengte
            </th>
            <th>
                Afmeting [cm]
            </th>
            <th>
                Streepje [cm]
            </th>
            <th>
                Locatie
            </th>
        </tr>

        @foreach ($saw_drawings as $index => $saw_drawing)
            <tr>
                <th>
                    Lengte {{ $index + 1 }}
                </th>
                <th>
                    @foreach ($saw_drawing as $length)
                        {{ $length['length'] / 10 }} <br>
                    @endforeach
                </th>
                <th>
                    @foreach ($saw_drawing as $length)
                        {{ $length['line'] / 10 }} <br>
                    @endforeach
                </th>
                <th>
                    @foreach ($saw_drawing as $length)
                        {{ $length['location'] }} <br>
                    @endforeach
                </th>
            </tr>
        @endforeach
    </table>
</div>

{{--@foreach($saw_drawings as $saw_drawing)--}}
{{--    <table--}}
{{--        style="width: 500px">--}}
{{--        <tbody>--}}

{{--        </tbody>--}}
{{--    </table>--}}
{{--    <table--}}
{{--        style="width: 500px">--}}
{{--        <tbody>--}}


{{--        </tbody>--}}
{{--    </table>--}}
{{--    @if($loop->last === false)--}}
{{--        <div class="page-break"></div>--}}
{{--    @endif--}}
{{--@endforeach--}}
</body>
</html>
