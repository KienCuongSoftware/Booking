<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-bcom-blue border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide shadow-md shadow-bcom-blue/25 hover:bg-bcom-blue/90 focus:outline-none focus:ring-2 focus:ring-bcom-blue focus:ring-offset-2 focus:ring-offset-white active:bg-bcom-navy transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
