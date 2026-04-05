@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full px-4 py-2.5 border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20 rounded-xl shadow-sm transition duration-150 disabled:bg-gray-50 disabled:text-gray-500']) }}>
