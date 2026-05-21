import { SVGAttributes } from 'react';

export default function GtechxLogoIcon({ className = 'h-10 w-10', ...props }: SVGAttributes<SVGSVGElement>) {
    return (
        <svg className={className} viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg" {...props}>
            <defs>
                <linearGradient id="gtechxNavG" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stopColor="#00c9a7" />
                    <stop offset="55%" stopColor="#f0a500" stopOpacity="0.5" />
                    <stop offset="100%" stopColor="#00c9a7" />
                </linearGradient>
            </defs>
            <circle cx="26" cy="26" r="24" fill="none" stroke="url(#gtechxNavG)" strokeWidth="2.3" />
            <line x1="26" y1="1.2" x2="26" y2="5.5" stroke="#00c9a7" strokeWidth="2" />
            <line x1="26" y1="46.5" x2="26" y2="50.8" stroke="#00c9a7" strokeWidth="2" />
            <line x1="1.2" y1="26" x2="5.5" y2="26" stroke="#f0a500" strokeWidth="2" />
            <line x1="46.5" y1="26" x2="50.8" y2="26" stroke="#f0a500" strokeWidth="2" />
            <circle cx="26" cy="26" r="19.5" fill="#04091a" stroke="rgba(0,201,167,0.2)" strokeWidth="1" />
            <circle cx="26" cy="26" r="13" fill="none" stroke="#00c9a7" strokeWidth="1.8" />
            <ellipse cx="26" cy="26" rx="6.5" ry="13" fill="none" stroke="#00c9a7" strokeWidth="1.1" opacity="0.85" />
            <line x1="13" y1="26" x2="39" y2="26" stroke="#00c9a7" strokeWidth="1.3" />
            <circle cx="26" cy="13" r="2.5" fill="#f0a500" />
            <circle cx="26" cy="39" r="2.5" fill="#f0a500" />
        </svg>
    );
}
