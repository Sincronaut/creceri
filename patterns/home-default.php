<?php
/**
 * Title: Home Layout (default)
 * Slug: child/home-layout
 * Categories: creceri-layouts
 * Block Types: core/post-content
 * Description: Default home layout with hero banner, cards, guides, and team highlights.
 */
?>
<!-- wp:child/banner {
    "brand":"Creceri",
    "line":"E-commerce, UX, and Digital Knowledge 1",
    "brandSize":80,
    "bgSpot":"left",
    "bgAngle":180,
    "bgStatus":"On",
    "imgShadow":"Off",
    "lead":"Discover clear, accessible insights into e-commerce, UX design, and digital strategy. Learn from real-world frameworks and web development concepts.",
    "imagePosition":"right",
    "imageLocation":"bottom",
    "imageFlip":"none",
    "imageSize":"xl",
    "buttonType":"Button",
    "buttonText":"Explore",
    "buttonUrl":"#explore",
    "buttonClass":"btn btn-custom text-white",
    "textAlign":"right",
    "textScale":1.1,
    "image":{
        "src":"https://creceri.com/wp-content/uploads/2025/09/banner.png",
        "alt":"3D illustration",
        "loading":"eager",
        "decoding":"async"
    }
} /-->

<!-- wp:child/card {
"title":"What�s New?",
"intro":"Stay up to date with the latest trends, tools, and success stories shaping the digital landscape. Get inspired by fresh perspectives and timely updates from industry leaders.",
"ctaText":"Browse All Updates",
"ctaUrl":"#all-updates",
"cards":[
    {
    "title":"Shopify Development",
    "text":"Lorem ipsum dolor sit amet consectetur adipiscing elit.",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card/card-1.jpg","alt":"Storefront and shopping icons"}
    },
    {
    "title":"Custom Development",
    "text":"Lorem ipsum dolor sit amet consectetur adipiscing elit.",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card/card-2.jpg","alt":"Gears and code blocks illustration"}
    },
    {
    "title":"UI/UX App Design",
    "text":"Lorem ipsum dolor sit amet consectetur adipiscing elit.",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card/card-3.jpg","alt":"UI screens and app design illustration"}
    },
    {
    "title":"Social Media Marketing",
    "text":"Lorem ipsum dolor sit amet consectetur adipiscing elit.",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card/card-4.jpg","alt":"Megaphone and social icons"}
    }
]
} /-->

<!-- wp:child/guides {
"title":"Guides &amp; Insights",
"intro":"Explore actionable guides and expert tips covering e-commerce, design, marketing, and more.<br>Our resources are built to help SMEs adapt, innovate, and thrive in the digital age.",
"items":[
    {
    "title":"E-commerce Development",
    "text":"Build, optimize, and scale your online store with practical frameworks and integrations.",
    "url":"#",
    "active":true,
    "bgColor":"white",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card-animation/card-animation-1.png","alt":"E-commerce development artwork"}
    },
    {
    "title":"UX Strategy",
    "text":"Research-driven UX patterns to improve conversion and retention across journeys.",
    "url":"#",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card-animation/card-animation-2.png","alt":"UX strategy artwork"}
    },
    {
    "title":"Content Marketing",
    "text":"Editorial workflows and SEO tips for sustainable growth.",
    "url":"#",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card-animation/card-animation-3.png","alt":"Content marketing artwork"}
    },
    {
    "title":"Performance &amp; Analytics",
    "text":"Measure, iterate, and ship faster with clear metrics.",
    "url":"#",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card-animation/card-animation-4.png","alt":"Performance and analytics artwork"}
    },
    {
    "title":"Case Studies",
    "text":"Real-world lessons from product launches and redesigns.",
    "url":"#",
    "image":{"src":"https://creceri.com/wp-content/themes/vite-ttf-child-creceri/assets/images/card-animation/card-animation-5.png","alt":"Case studies artwork"}
    }
]
} /-->

<!-- wp:child/card-blogs {
    "title": "Tek Stories",
    "intro": "Explore inspiring stories from SMEs and tech pioneers who successfully navigated digital transformation. Discover real-world strategies and practical solutions you can apply to elevate your own business journey.",
    "ctaText": "Browse All Stories",
    "ctaUrl": "#",
    "ctaBgColor": "#962E2A",
    "sectionBgColor": "#FAFAFA",
    "showReadLink": true,
    "readLinkText": "Read More",
    "data_count": 4,
    "animation": "Off",

    "overlayEnabled": true,
    "overlayColor": "#000000",
    "overlayOpacity": 0.55,
    "overlaySize": 60,
    "overlayPosition": "bottom",
    "imagePosition": "center",

    "items": [
        {
        "image": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-blogs/asset/card-1.png",
        "heading": "Search Engine Optimization",
        "text": "SEO enhances visibility, driving organic growth for your website. Our expert strategies improve rankings, traffic, and long-term online presence.",
        "url": "#",
        "overlay": {
            "enabled": true,
            "color": "#962E2A",
            "opacity": 0.40,
            "size": 70,
            "position": "top"
        }
        },
        {
        "image": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-blogs/asset/card-2.png",
        "heading": "WordPress Development",
        "text": "WordPress Development offers flexible, SEO-friendly solutions. We build custom themes and plugins for secure, high-performing websites.",
        "url": "#",
        "overlay": { "enabled": false }
        },
        {
        "image": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-blogs/asset/card-3.png",
        "heading": "Magento Development",
        "text": "Magento Development delivers scalable solutions for eCommerce growth. Our services build secure, customized, and user-friendly stores.",
        "url": "#"
        },
        {
        "image": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-blogs/asset/card-4.jpg",
        "heading": "Shopify Development",
        "text": "Shopify Development provides scalable solutions for online growth. We design secure, user-friendly stores that elevate customer experiences.",
        "url": "#"
        }
    ]
} /-->

<!-- wp:child/card-team {
    "title": "Who We Are?",
    "content": "Creceri is a knowledge hub empowering SMEs to thrive through technology, insights, and real-world stories. We deliver expert guides, actionable strategies, and inspiring case studies. Our mission is growth. We simplify digital transformation. Your success is our driving purpose.",
    "ctaText": "Learn More",
    "ctaUrl": "#",
    "btnClass": "btn-primary",
    "reverse": false,
    "sectionId": "who-title",
    "gallery": [
        { "src": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-team/asset/team-1.jpg", "alt": "" },
        { "src": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-team/asset/team-2.jpg", "alt": "" },
        { "src": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-team/asset/team-3.jpg", "alt": "" },
        { "src": "https://creceri.com/wp-content/themes/vite-ttf-child-creceri/blocks/card-team/asset/team-4.jpg", "alt": "" }
    ]
} /-->