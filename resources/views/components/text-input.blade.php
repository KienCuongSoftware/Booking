@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full px-4 py-2.5 border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 rounded-xl shadow-sm transition duration-150 disabled:bg-gray-50 disabled:text-gray-500']) }}>
