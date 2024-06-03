/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.antlers.html',
        './resources/**/*.antlers.php',
        './resources/**/*.blade.php',
        './resources/**/*.vue',
        './content/**/*.md',
    ],

    theme: {
        extend: {
            fontFamily: {
                'headline': ['Assistant', 'sans-serif'],
                'title': ['Assistant', 'sans-serif'],
                'regular': ['Assistant', 'sans-serif'],
            },
            padding: {
                '29': '7rem',
                '-29': '-7rem',
                '-36': '-10rem'
            },
            margin: {
                '29': '7rem',
                '36': '10rem',
                '72': '22rem',
                '128': '48rem',
                '-22': '-5rem',
                '-26': '-6rem',
                '-29': '-7rem',
                '-36': '-10rem',
                '-72': '-22rem',
                '-74': '-24rem',
                '-82': '-28rem',
            },
            height: {
                '300': '300px',
                '350': '350px',
                '500': '500px',
                '750': '750px',
            },
            width: {
                '500': '500px',
            },
            zIndex: {
                '0': '0',
                '10': '10',
                '-10': '-10',
                '20': '20',
                '30': '30',
                '40': '40',
                '50': '50',
                '25': '25',
                '50': '50',
                '75': '75',
                '100': '100',
                'auto': 'auto',
            },
        },
    },

    plugins: [
        require('@tailwindcss/typography'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/aspect-ratio'),
    ],
};
