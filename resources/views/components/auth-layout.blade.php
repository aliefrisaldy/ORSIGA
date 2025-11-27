<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - Telkom Indonesia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'telkom-red': '#E53E3E',
                        'telkom-gray': '#6B7280',
                    }
                }
            }
        }
    </script>
    <style>
        .tagline {
            text-align: center;
            margin-bottom: 1rem;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e293b 0%, #475569 50%, #E53E3E 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .tagline::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #E53E3E, #dc2626);
            margin: 0.5rem auto;
            border-radius: 2px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
    </style>

</head>

<body class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Changed from red gradient to light gray gradient for better balance -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-4">
            <!-- Improved logo container with better centering and spacing -->
            <div class="text-center">
                <div class="mx-auto h-32 w-32 bg-white rounded-full shadow-lg flex items-center justify-center mb-6">
                    <img src="{{ asset('images/Logo_Login.png') }}" alt="Telkom Indonesia"
                        class="h-24 w-24 object-contain">
                </div>
                <h1 class="tagline">The World In Your Hand</h1>
                <p class="subtitle" style="margin-bottom:0;">Sign In To Your Account</p>
            </div>

            <!-- Updated card design with white background and subtle shadow -->
            <div class="bg-white rounded-xl shadow-xl p-8 border border-gray-200">
                @yield('content')
            </div>

            <!-- Added footer with gray text for better balance -->
            <div class="text-center">
                <p class="text-sm text-telkom-gray">
                    © {{ date('Y') }} PT. Telkom Indonesia. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>

</html>