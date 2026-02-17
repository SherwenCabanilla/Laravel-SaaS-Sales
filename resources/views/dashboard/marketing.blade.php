@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p>This is your Marketing Dashboard.</p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3>Campaign Overview</h3>
        <p>Track your marketing funnels and lead generation.</p>
        
        <div style="margin-top: 20px;">
             <!-- Static Placeholder Chart -->
             <div style="background: #f9fafb; height: 200px; display: flex; align-items: center; justify-content: center; border: 2px dashed #d1d5db; border-radius: 6px;">
                <span style="color: #6b7280;">Marketing Performance Chart Placeholder</span>
             </div>
        </div>
    </div>
@endsection
