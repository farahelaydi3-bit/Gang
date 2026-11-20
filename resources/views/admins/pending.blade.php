<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Owner Application | CourtBook</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #1d2939;
            font-family: Manrope, sans-serif;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 20px 7%;
            background: white;
            border-bottom: 1px solid #e7eaf0;
        }

        .brand {
            color: #147d64;
            font-size: 24px;
            font-weight: 800;
            text-decoration: none;
        }

        .logout {
            padding: 10px 18px;
            border: 1px solid #e0e4eb;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            font: inherit;
        }

        .container {
            max-width: 850px;
            margin: 45px auto;
            padding: 0 20px 50px;
        }

        .card {
            background: white;
            padding: 34px;
            border: 1px solid #e8ebf1;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .035);
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }

        h2 {
            font-size: 18px;
            margin: 30px 0 16px;
        }

        .muted {
            color: #667085;
            line-height: 1.7;
        }

        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        input:not([type=checkbox]),
        textarea {
            display: block;
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d6dce5;
            border-radius: 10px;
            font: inherit;
            background: white;
        }

        textarea {
            min-height: 115px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: 2px solid #b6e4d9;
            border-color: #147d64;
        }

        .sports-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .sport-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px;
            border: 1px solid #e0e4eb;
            border-radius: 12px;
            cursor: pointer;
        }

        .sport-option input {
            accent-color: #147d64;
        }

        .sport-option span {
            font-weight: 600;
        }

        .button {
            display: inline-block;
            border: none;
            border-radius: 10px;
            background: #147d64;
            color: white;
            padding: 14px 22px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        .button:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px;
            margin-bottom: 22px;
            border-radius: 10px;
            line-height: 1.6;
        }

        .success {
            background: #e9f8f0;
            color: #116747;
        }

        .error {
            background: #fff0ed;
            color: #a23427;
        }

        .info {
            background: #edf4ff;
            color: #31578d;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 800;
            background: #fff3d9;
            color: #996000;
        }

        .summary {
            margin-top: 24px;
            padding: 20px;
            border: 1px solid #e8ebf1;
            border-radius: 12px;
        }

        .summary p {
            overflow-wrap: anywhere;
            line-height: 1.7;
        }

        .hint {
            font-size: 12px;
            color: #667085;
            margin-top: 7px;
        }

        .validation-error {
            color: #b42318;
            font-size: 12px;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .card {
                padding: 22px;
            }

            .sports-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        <a href="{{ route('courtbook.home') }}" class="brand">
            CourtBook
        </a>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="logout" type="submit">Logout</button>
        </form>
    </header>

    <main class="container">

        @if (session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert error">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert error">
            Please correct the highlighted fields.
        </div>
        @endif

        @if ($application && $application->status === 'submitted')

        <!-- Application Under Review -->
        <div class="card">
            <span class="status">Under Review</span>

            <h1 style="margin-top:20px">
                Application Submitted!
            </h1>

            <p class="muted">
                Hello {{ $admin->name }}, your owner application
                is currently being reviewed by CourtBook administration.
            </p>

            <div class="alert info">
                Your account will remain pending until the
                Super Admin approves your application.
            </div>

            <div class="summary">
                <h2 style="margin-top:0">Application Summary</h2>

                <p>
                    <strong>Facility:</strong>
                    {{ $application->facility_name }}
                </p>

                <p>
                    <strong>Address:</strong>
                    {{ $application->address }}
                </p>

                <p>
                    <strong>Number of Courts:</strong>
                    {{ $application->courts_count }}
                </p>

                <p>
                    <strong>Sports:</strong>
                    {{ $application->sports->pluck('name')->join(', ') }}
                </p>

                <p>
                    <strong>Submitted:</strong>
                    {{ $application->submitted_at?->format('M d, Y - h:i A') }}
                </p>

                <p>
                    <strong>Attachments:</strong>
                    {{ $application->attachments->count() }} file(s)
                </p>
            </div>
        </div>

        @else

        <div class="card">

            @if ($application && $application->status === 'rejected')

            <div class="alert error">
                <strong>Your previous application was rejected.</strong>

                <p>
                    {{ $application->review_notes ?: 'No reason was provided.' }}
                </p>

                You can submit a new application below.
            </div>

            @elseif ($admin->status === 'rejected')

            <div class="alert error">
                Your owner account was rejected.
                You can submit a new application for review.
            </div>

            @endif

            <h1>Become a CourtBook Owner</h1>

            <p class="muted">
                Hello {{ $admin->name }}!
                Complete this application so our team can review
                your sports facility and activate your owner account.
            </p>

            <form
                action="{{ route('admin.application.store') }}"
                method="POST"
                enctype="multipart/form-data"
                id="applicationForm">
                @csrf

                <h2>Facility Information</h2>

                <div class="field">
                    <label for="facility_name">Facility Name *</label>

                    <input
                        id="facility_name"
                        name="facility_name"
                        type="text"
                        maxlength="255"
                        required
                        value="{{ old('facility_name', $admin->business_name) }}"
                        placeholder="e.g. CourtBook Sports Center">

                    @error('facility_name')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="address">Facility Address *</label>

                    <input
                        id="address"
                        name="address"
                        type="text"
                        maxlength="255"
                        required
                        value="{{ old('address') }}"
                        placeholder="City, area, street">

                    @error('address')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="courts_count">Number of Courts *</label>

                    <input
                        id="courts_count"
                        name="courts_count"
                        type="number"
                        min="1"
                        max="1000"
                        required
                        value="{{ old('courts_count') }}">

                    @error('courts_count')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <h2>Available Sports</h2>

                <p class="muted">
                    Select all sports offered at your facility.
                </p>

                <div class="sports-grid">
                    @forelse ($sports as $sport)
                    <label class="sport-option">
                        <input
                            type="checkbox"
                            name="sports[]"
                            value="{{ $sport->id }}"
                            @checked(in_array($sport->id, old('sports', [])))
                        >

                        <span>{{ $sport->name }}</span>
                    </label>
                    @empty
                    <div class="alert info">
                        No sports are available yet.
                        Please contact CourtBook administration.
                    </div>
                    @endforelse
                </div>

                @error('sports')
                <div class="validation-error">{{ $message }}</div>
                @enderror

                @error('sports.*')
                <div class="validation-error">{{ $message }}</div>
                @enderror

                <h2>About Your Facility</h2>

                <div class="field">
                    <label for="facility_description">
                        Facility Description *
                    </label>

                    <textarea
                        id="facility_description"
                        name="facility_description"
                        maxlength="3000"
                        required
                        placeholder="Describe your facility, amenities, and services...">{{ old('facility_description') }}</textarea>

                    @error('facility_description')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <h2>Application Letter</h2>

                <div class="field">
                    <label for="application_message">
                        Message to CourtBook Administration *
                    </label>

                    <textarea
                        id="application_message"
                        name="application_message"
                        maxlength="5000"
                        required
                        placeholder="Introduce yourself and explain why you want to join CourtBook...">{{ old('application_message') }}</textarea>

                    @error('application_message')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <h2>Supporting Documents</h2>

                <div class="field">
                    <label for="attachments">
                        Photos or Documents (Optional)
                    </label>

                    <input
                        id="attachments"
                        type="file"
                        name="attachments[]"
                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                        multiple>

                    <p class="hint">
                        Up to 5 files. Maximum 5 MB per file.
                        Accepted: JPG, PNG, WEBP, PDF.
                    </p>

                    @error('attachments')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror

                    @error('attachments.*')
                    <div class="validation-error">{{ $message }}</div>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="button"
                    id="submitButton"
                    @disabled($sports->isEmpty())
                    >
                    Submit Application
                </button>
            </form>
        </div>

        @endif

    </main>

    <script>
        const form = document.getElementById('applicationForm');

        if (form) {
            form.addEventListener('submit', function(event) {
                const sports = form.querySelectorAll(
                    'input[name="sports[]"]:checked'
                );

                if (sports.length === 0) {
                    event.preventDefault();
                    alert('Please select at least one sport.');
                    return;
                }

                const button = document.getElementById('submitButton');
                button.disabled = true;
                button.textContent = 'Submitting...';
            });
        }
    </script>

</body>

</html>