<footer class="container z-10 px-6 py-6 mx-auto mt-6 font-mono border-t md:px-0 md:border-none">
    <div class="flex flex-wrap items-center mt-4 space-x-5 md:justify-start">
        <a href="{{ config('seo.social.x') }}" rel="me" class="flex items-center w-5 h-5 text-gray-500 hover:text-black" title="X" aria-label="X">
            <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
        </a>
        <a href="{{ config('seo.social.instagram') }}" rel="me" class="flex items-center w-5 h-5 text-gray-500 hover:text-black" title="Instagram" aria-label="Instagram">
            <i class="fa-brands fa-instagram" aria-hidden="true"></i>
        </a>
        <a href="{{ config('seo.social.github') }}" rel="me" class="flex items-center w-5 h-5 text-gray-500 hover:text-black" title="GitHub" aria-label="GitHub">
            <i class="fa-brands fa-github" aria-hidden="true"></i>
        </a>
        <a href="{{ config('seo.social.linkedin') }}" rel="me" class="flex items-center w-5 h-5 text-gray-500 hover:text-black" title="LinkedIn" aria-label="LinkedIn">
            <i class="fa-brands fa-linkedin" aria-hidden="true"></i>
        </a>
        <a href="/contact" class="flex items-center w-5 h-5 text-gray-500 hover:text-black" title="Email" aria-label="Contact">
            <i class="fa-light fa-envelope" aria-hidden="true"></i>
        </a>
    </div>
    <p class="pt-4 text-xs text-gray-500">&copy;{{ now()->year }} Sheldon Kotyk</p>
</footer>
