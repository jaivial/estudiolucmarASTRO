// Import necessary modules
import { defineConfig } from "astro/config";
import tailwind from "@astrojs/tailwind";
import react from "@astrojs/react";
import dotenv from "dotenv";

// Load environment variables from .env file
dotenv.config();

// Define and export the configuration
export default defineConfig({
  // Integrations configuration, adding Tailwind CSS
  integrations: [
    tailwind(),
    // Add more integrations if needed
    react(),
  ],
  define: { "process.env": process.env },
});
