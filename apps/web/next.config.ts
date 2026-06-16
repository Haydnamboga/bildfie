import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  reactStrictMode: true,
  // Transpile workspace packages consumed as TypeScript source.
  transpilePackages: [
    "@bildfie/ui",
    "@bildfie/auth",
    "@bildfie/types",
    "@bildfie/api-client",
    "@bildfie/validation",
  ],
};

export default nextConfig;
