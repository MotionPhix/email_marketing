<table>
  <thead>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
  </tr>
  </thead>
  <tbody>
  @foreach ($recipients as $recipient)
    <tr>
      <td>{{ $recipient->id }}</td>
      <td>{{ $recipient->name }}</td>
      <td>{{ $recipient->email }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
