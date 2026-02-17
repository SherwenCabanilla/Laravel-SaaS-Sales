@extends('layouts.admin')

@section('title', 'Sales Dashboard')

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p>This is your Sales Dashboard.</p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3>Your Leads</h3>
        <p>View and manage your assigned leads.</p>
        
        <div style="margin-top: 20px; display: flex; gap: 20px;">
            <a href="{{ route('leads.index') }}" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">View All Leads</a>
            <a href="{{ route('leads.create') }}" style="background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;">Add New Lead</a>
        </div>
    </div>
@endsection
