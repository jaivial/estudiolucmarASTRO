// Import necessary modules
import { defineConfig } from "astro/config";
import tailwind from "@astrojs/tailwind";

// Define and export the configuration
import react from "@astrojs/react";

// https://astro.build/config
export default defineConfig({
  // Integrations configuration, adding Tailwind CSS
  integrations: [tailwind()
  // Add more integrations if needed
  , react()]
});