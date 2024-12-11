<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deadline Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            background-color: #ffffff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #5d5c61;
            margin: 0;
        }
        .content {
            line-height: 1.6;
        }
        .content h2 {
            font-size: 20px;
            color: #379683;
            margin: 0 0 10px;
        }
        .content p {
            font-size: 16px;
            color: #555;
            margin: 10px 0;
        }
        .content .cta-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #379683;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>⚠️ Deadline Alert</h1>
        </div>
        <div class="content">
            <h2>Task: {{ $task->title }}</h2>
            <p>Hello {{ $username }},</p>
            <p>This is a reminder that the deadline for the task <strong>{{ $task->title }}</strong> is approaching.</p>
            <p><strong>Deadline:</strong> {{ $task->end_date->format('F j, Y, g:i A') }}</p>
            <p>Please ensure that all necessary work is completed before the deadline.</p>
            {{-- <a href="{{ $task->link }}" class="cta-button">View Task</a> --}}
        </div>
        <div class="footer">
            <p>Thank you for your attention to this matter.</p>
            <p>&copy; 2024 Your Company</p>
        </div>
    </div>
</body>
</html>
