<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Timezone</title>
</head>
<body>
    <p>php date {{ date('Y-m-d H:i:s') }}</p>
    <p>carbon date {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
    <p>javascript moment date <span id="moment-date"></span></p>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.37/moment-timezone-with-data.min.js"></script>
    <script>
        window.onload = function() {
            var date = moment().tz('Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss')
            document.getElementById("moment-date").innerHTML = date;
        };
    </script>
</body>
</html>
