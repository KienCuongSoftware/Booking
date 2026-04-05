<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white border border-slate-200 rounded-xl font-semibold text-sm text-bcom-navy tracking-wide shadow-sm hover:bg-sky-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-bcom-blue/30 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
