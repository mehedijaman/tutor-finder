<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tutor CV</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 24px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        h2 {
            margin-top: 22px;
            margin-bottom: 8px;
            font-size: 16px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
        }

        .muted {
            color: #475569;
        }

        .section {
            margin-top: 8px;
        }

        .row {
            margin-bottom: 4px;
        }

        .label {
            font-weight: 700;
        }

        .education-item {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #cbd5e1;
        }

        .pill {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 2px 8px;
            margin: 2px 4px 2px 0;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <h1>{{ $user->name }}</h1>
    <p class="muted">Tutor ID: #{{ $user->id }}</p>

    <div class="section">
        <div class="row"><span class="label">Email:</span> {{ $user->email }}</div>
        <div class="row"><span class="label">Phone:</span> {{ $user->phone ?: 'N/A' }}</div>
        <div class="row"><span class="label">Verification:</span> {{ $user->verification_status ?: 'unverified' }}</div>
    </div>

    <h2>Personal Information</h2>
    <div class="section">
        <div class="row"><span class="label">Gender:</span> {{ $profile?->gender ?: 'N/A' }}</div>
        <div class="row"><span class="label">Date of Birth:</span> {{ $profile?->date_of_birth?->format('Y-m-d') ?: 'N/A' }}</div>
        <div class="row"><span class="label">Present Address:</span> {{ $profile?->present_address ?: 'N/A' }}</div>
        <div class="row"><span class="label">Permanent Address:</span> {{ $profile?->permanent_address ?: 'N/A' }}</div>
        <div class="row"><span class="label">NID No:</span> {{ $profile?->nid_no ?: 'N/A' }}</div>
        <div class="row"><span class="label">Bio:</span> {{ $profile?->bio ?: 'N/A' }}</div>
    </div>

    <h2>Education</h2>
    @if ($educations->isEmpty())
        <p class="muted">No education records added yet.</p>
    @else
        @foreach ($educations as $education)
            <div class="education-item">
                <div class="row"><span class="label">Degree:</span> {{ $education->degree }}</div>
                <div class="row"><span class="label">Institute:</span> {{ $education->institute }}</div>
                <div class="row"><span class="label">Department:</span> {{ $education->department ?: 'N/A' }}</div>
                <div class="row"><span class="label">Graduation Year:</span> {{ $education->graduation_year ?: 'N/A' }}</div>
                <div class="row"><span class="label">Result:</span> {{ $education->result ?: 'N/A' }}</div>
                <div class="row"><span class="label">Current:</span> {{ $education->is_current ? 'Yes' : 'No' }}</div>
            </div>
        @endforeach
    @endif

    <h2>Tuition Preferences</h2>
    <div class="section">
        <div class="row"><span class="label">Expected Salary:</span>
            {{ $profile?->expected_salary_min ? 'BDT '.$profile->expected_salary_min : 'N/A' }}
            -
            {{ $profile?->expected_salary_max ? 'BDT '.$profile->expected_salary_max : 'N/A' }}
        </div>
        <div class="row"><span class="label">Available Time:</span> {{ $profile?->available_time ?: 'N/A' }}</div>

        <div class="row">
            <span class="label">Available Days:</span>
            @foreach (($profile?->available_days ?? []) as $day)
                <span class="pill">{{ strtoupper((string) $day) }}</span>
            @endforeach
        </div>

        <div class="row">
            <span class="label">Preferred Tuition Types:</span>
            @foreach (($profile?->preferred_tuition_types ?? []) as $item)
                <span class="pill">{{ (string) $item }}</span>
            @endforeach
        </div>

        <div class="row">
            <span class="label">Preferred Categories:</span>
            @foreach (($profile?->preferred_categories ?? []) as $item)
                <span class="pill">{{ (string) $item }}</span>
            @endforeach
        </div>

        <div class="row">
            <span class="label">Preferred Classes:</span>
            @foreach (($profile?->preferred_classes ?? []) as $item)
                <span class="pill">{{ (string) $item }}</span>
            @endforeach
        </div>

        <div class="row">
            <span class="label">Preferred Subjects:</span>
            @foreach (($profile?->preferred_subjects ?? []) as $item)
                <span class="pill">{{ (string) $item }}</span>
            @endforeach
        </div>

        <div class="row">
            <span class="label">Preferred Locations:</span>
            @foreach (($profile?->preferred_locations ?? []) as $item)
                <span class="pill">{{ (string) $item }}</span>
            @endforeach
        </div>
    </div>
</body>

</html>
