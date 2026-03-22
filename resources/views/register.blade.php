<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resident Registration - e-Governance System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    <span class="text-red-600">e-Governance</span> 
                    <span class="text-blue-600">System</span>
                </h1>
                <h2 class="text-2xl font-semibold text-gray-700">Resident Registration</h2>
                <p class="mt-2 text-sm text-gray-600">Please fill out all required information to register as a resident</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-8 bg-white shadow-xl rounded-lg p-8">
                @csrf

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section 1: Personal Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">1</span>
                        Personal Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                First Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   name="first_name" 
                                   id="first_name" 
                                   value="{{ old('first_name') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Middle Name
                            </label>
                            <input type="text" 
                                   name="middle_name" 
                                   id="middle_name" 
                                   value="{{ old('middle_name') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('middle_name') border-red-500 @enderror">
                            @error('middle_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Last Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   name="last_name" 
                                   id="last_name" 
                                   value="{{ old('last_name') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Suffix -->
                        <div>
                            <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1">
                                Suffix (Jr., Sr., III, etc.)
                            </label>
                            <input type="text" 
                                   name="suffix" 
                                   id="suffix" 
                                   value="{{ old('suffix') }}"
                                   placeholder="e.g., Jr., Sr., III"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('suffix') border-red-500 @enderror">
                            @error('suffix')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Address Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">2</span>
                        Address Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- House Number -->
                        <div>
                            <label for="house_no" class="block text-sm font-medium text-gray-700 mb-1">
                                House Number <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   name="house_no" 
                                   id="house_no" 
                                   value="{{ old('house_no') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('house_no') border-red-500 @enderror">
                            @error('house_no')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Purok -->
                        <div>
                            <label for="purok" class="block text-sm font-medium text-gray-700 mb-1">
                                Purok <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   name="purok" 
                                   id="purok" 
                                   value="{{ old('purok') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('purok') border-red-500 @enderror">
                            @error('purok')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- Section 3: Contact & Demographics -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">3</span>
                        Contact & Demographics
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- Contact Number -->
                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">
                                Contact Number <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   name="contact_number" 
                                   id="contact_number" 
                                   value="{{ old('contact_number') }}"
                                   required
                                   placeholder="09XX XXX XXXX"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('contact_number') border-red-500 @enderror">
                            @error('contact_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Age -->
                        <div>
                            <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
                                Age <span class="text-red-600">*</span>
                            </label>
                            <input type="number" 
                                   name="age" 
                                   id="age" 
                                   value="{{ old('age') }}"
                                   required
                                   min="1"
                                   max="150"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('age') border-red-500 @enderror">
                            @error('age')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                                Gender <span class="text-red-600">*</span>
                            </label>
                            <select name="gender" 
                                    id="gender" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('gender') border-red-500 @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birthdate -->
                        <div>
                            <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-1">
                                Birthdate <span class="text-red-600">*</span>
                            </label>
                            <input type="date" 
                                   name="birthdate" 
                                   id="birthdate" 
                                   value="{{ old('birthdate') }}"
                                   required
                                   max="{{ now('Asia/Manila')->format('Y-m-d') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('birthdate') border-red-500 @enderror">
                            @error('birthdate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Civil Status -->
                        <div>
                            <label for="civil_status" class="block text-sm font-medium text-gray-700 mb-1">
                                Civil Status <span class="text-red-600">*</span>
                            </label>
                            <select name="civil_status" 
                                    id="civil_status" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('civil_status') border-red-500 @enderror">
                                <option value="">Select Civil Status</option>
                                <option value="single" {{ old('civil_status') == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ old('civil_status') == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="widowed" {{ old('civil_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                <option value="divorced" {{ old('civil_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="separated" {{ old('civil_status') == 'separated' ? 'selected' : '' }}>Separated</option>
                            </select>
                            @error('civil_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Head of Family -->
                        <div>
                            <label for="head_of_family" class="block text-sm font-medium text-gray-700 mb-1">
                                Head of Family <span class="text-red-600">*</span>
                            </label>
                            <select name="head_of_family" 
                                    id="head_of_family" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('head_of_family') border-red-500 @enderror">
                                <option value="">Select Option</option>
                                <option value="yes" {{ old('head_of_family') == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="no" {{ old('head_of_family') == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('head_of_family')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Resident Type -->
                        <div>
                            <label for="resident_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Resident Type <span class="text-red-600">*</span>
                            </label>
                            <select name="resident_type" 
                                    id="resident_type" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('resident_type') border-red-500 @enderror">
                                <option value="">Select Resident Type</option>
                                <option value="permanent" {{ old('resident_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="non-permanent" {{ old('resident_type') == 'non-permanent' ? 'selected' : '' }}>Non-Permanent</option>
                            </select>
                            @error('resident_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Account Information -->
                <div class="pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">4</span>
                        Account Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address <span class="text-red-600">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="email"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Password <span class="text-red-600">*</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required
                                   autocomplete="new-password"
                                   minlength="8"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="md:col-span-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password <span class="text-red-600">*</span>
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   required
                                   autocomplete="new-password"
                                   minlength="8"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Register
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 mt-6">
                <p>&copy; {{ date('Y') }} e-Governance System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
