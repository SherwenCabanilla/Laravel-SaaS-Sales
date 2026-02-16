<!-- resources/views/leads/index.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Leads - SaaS Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f7f7f7;
            color: #333;
        }

        h1, h3 {
            color: #2c3e50;
        }

        input, button {
            padding: 5px 10px;
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #ecf0f1;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Leads for Tenant: {{ auth()->user()->tenant_id }}</h1>

    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    <h3>Add a New Lead</h3>
    <form method="POST" action="{{ route('leads.store') }}">
        @csrf
        <label>Name:</label><br>
        <input type="text" name="name" placeholder="Name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" placeholder="Email" required><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" placeholder="Phone"><br><br>

        <button type="submit">Add Lead</button>
    </form>

    <h3>Existing Leads</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Score</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr>
                <td>{{ $lead->id }}</td>
                <td>{{ $lead->name }}</td>
                <td>{{ $lead->email }}</td>
                <td>{{ $lead->phone }}</td>
                <td>{{ $lead->status ?? 'N/A' }}</td>
                <td>{{ $lead->score ?? 'N/A' }}</td>
                <td>
                    <!-- Delete form -->
                    <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Delete this lead?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">No leads yet!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
