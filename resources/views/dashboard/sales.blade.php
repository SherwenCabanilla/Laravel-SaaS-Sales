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
        
        <div style="margin-top: 20px; display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="{{ route('leads.index') }}" style="background: var(--theme-primary, #2563EB); color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600;">View All Leads</a>
            <a href="{{ route('leads.create') }}" style="background: var(--theme-accent, #0EA5E9); color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600;">Add New Lead</a>
        </div>
    </div>
@endsection
