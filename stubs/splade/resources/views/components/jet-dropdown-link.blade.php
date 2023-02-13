@props(['as' => false])

<div>
    @if($as === 'button')
        <button {{ $attributes->merge(['type' => 'submit', 'class' => 'block w-full px-4 py-2 text-sm leading-5 text-gray-700 text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition']) }}>
            {{ $slot }}
        </button>
    @elseif($as === 'a')
        <a {{ $attributes->merge(['class' => 'block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition']) }}>
            {{ $slot }}
        </a>
    @else
        <Link {{ $attributes->merge(['class' => 'block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition']) }}>
            {{ $slot }}
        </Link>
    @endif
</div>