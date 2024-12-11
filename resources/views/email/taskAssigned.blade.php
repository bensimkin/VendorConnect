<!DOCTYPE html>
<html>
<head>
    <title>{{ $notification_data['title'] }}</title>
</head>
<body>
    <h1>{{ $notification_data['title'] }}</h1>
    <p>{{ $notification_data['message'] }}</p>
    <p><strong>Task Title:</strong> {{ $notification_data['type_title'] }}</p>
    <p><strong>Task ID:</strong> #{{ $notification_data['type_id'] }}</p>
    <p>You can view the task here: <a href="{{ env('APP_URL') }}/{{ $notification_data['access_url'] }}">View Task</a></p>
    <p>Action: {{ $notification_data['action'] }}</p>
</body>
</html>
