@extends('layouts.admin')

@section('title', 'Account Owner Dashboard')

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p>This is your Account Owner Dashboard.</p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3>Quick Stats</h3>
        <p>Manage your team, leads, and subscription here.</p>
        <!-- Static placeholders -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: #eff6ff; padding: 15px; border-radius: 6px;">
                <h4 style="margin: 0; color: #1e40af;">Total Leads</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">125</p>
            </div>
            <div style="background: #f0fdf4; padding: 15px; border-radius: 6px;">
                <h4 style="margin: 0; color: #166534;">Active Users</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">5</p>
            </div>
        </div>
    </div>
@endsection
