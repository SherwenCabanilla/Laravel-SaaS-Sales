@extends('layouts.admin')

@section('title', 'Edit Lead')

@section('content')
    <div class="top-header">
        <h1>Edit Lead: {{ $lead->name }}</h1>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <!-- Edit Form -->
        <div class="card">
            <h3>Lead Details</h3>
            <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 20px;">
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold;">Name</label>
                    <input type="text" name="name" id="name" required 
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('name', $lead->name) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold;">Email</label>
                    <input type="email" name="email" id="email" required 
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('email', $lead->email) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="phone" style="display: block; margin-bottom: 8px; font-weight: bold;">Phone</label>
                    <input type="text" name="phone" id="phone" 
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('phone', $lead->phone) }}">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="status" style="display: block; margin-bottom: 8px; font-weight: bold;">Status</label>
                    <select name="status" id="status" required
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="score" style="display: block; margin-bottom: 8px; font-weight: bold;">Score</label>
                    <input type="number" name="score" id="score" 
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                        value="{{ old('score', $lead->score) }}">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" 
                        style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        Update Lead
                    </button>
                    <a href="{{ route('leads.index') }}" 
                        style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Activities / Notes -->
        <div class="card">
            <h3>Activity Log</h3>
            
            <div style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
                @forelse($lead->activities as $activity)
                    <div style="border-left: 2px solid #2563EB; padding-left: 10px; margin-bottom: 15px;">
                        <p style="font-size: 12px; color: #6B7280; margin-bottom: 4px;">
                            {{ $activity->created_at->format('M d, H:i') }} - <strong>{{ $activity->activity_type }}</strong>
                        </p>
                        <p style="font-size: 14px; color: #1F2937;">{{ $activity->notes }}</p>
                    </div>
                @empty
                    <p style="color: #6B7280; font-style: italic;">No activities recorded yet.</p>
                @endforelse
            </div>

            <hr style="border: 0; border-top: 1px solid #DBEAFE; margin: 15px 0;">

            <h4>Add Note</h4>
            <form action="{{ route('leads.activities.store', $lead->id) }}" method="POST">
                @csrf
                <input type="hidden" name="activity_type" value="Note">
                
                <textarea name="notes" rows="3" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px; margin-bottom: 10px;"
                    placeholder="Enter activity details..."></textarea>
                
                <button type="submit" 
                    style="padding: 8px 16px; background-color: #10B981; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Add Note
                </button>
            </form>
        </div>

    </div>
@endsection
