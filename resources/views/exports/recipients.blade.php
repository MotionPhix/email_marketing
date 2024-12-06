<!DOCTYPE html>
  <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet"/>
      <link href="https://fonts.googleapis.com/css2?family=Tangerine&display=swap" rel="stylesheet"/>
      <style>

        .m {
          font-family: 'Montserrat';
        }

        .t {
          font-family: 'Tangerine';
        }

      </style>
    </head>
  <body>

  <h1 class="m">
    Recipients information
  </h1>

  <div style="border: 1px solid #000; border-radius: 8px; overflow: hidden; width: 100%;">
    <table style="width: 100%; border-collapse: collapse;" class="t">
      <thead>
        <tr>
          <th style="padding: 8px; background-color: #f2f2f2; text-align: left;">Name</th>
          <th style="padding: 8px; background-color: #f2f2f2; text-align: left;">Email</th>
          <th style="padding: 8px; background-color: #f2f2f2; text-align: left;">Gender</th>
          <th style="padding: 8px; background-color: #f2f2f2; text-align: left;">Status</th>
        </tr>
      </thead>

      <tbody>
        @foreach ($recipients as $recipient)
          <tr>
            <td style="padding: 8px; border-bottom: 1px solid #000;">{{ $recipient->name }}</td>

            <td style="padding: 8px; border-bottom: 1px solid #000;">{{ $recipient->email }}</td>

            <td style="padding: 8px; border-bottom: 1px solid #000; text-transform: capitalize">
              {{ $recipient->gender }}
            </td>

            <td style="padding: 8px; border-bottom: 1px solid #000; text-transform: capitalize">
              {{ $recipient->status }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  </body>
</html>
