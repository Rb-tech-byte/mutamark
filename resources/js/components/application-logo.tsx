import { SVGAttributes } from "react";

export default function ApplicationLogo(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            {/* Left emerald arch */}
            <path
                d="M 17 92 L 17 32 C 17 12 36 12 50 64"
                stroke="#10b77f"
                strokeWidth="15"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            {/* Right main ribbon */}
            <path
                d="M 50 64 C 64 12 83 12 83 32 L 83 92"
                stroke="currentColor"
                strokeWidth="15"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            {/* Signature inner dot */}
            <circle cx="30" cy="50" r="7.5" fill="currentColor" />
        </svg>
    );
}
