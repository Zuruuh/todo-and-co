declare module "@symfony/stimulus-bridge" {
  import { Application } from "stimulus";

  // Actually stimulus serves its own types, but for the webpack context https://www.npmjs.com/package/@types/webpack-env is required.
  export function startStimulusApp(
    context: __WebpackModuleApi.RequireContext
  ): Application;
}
