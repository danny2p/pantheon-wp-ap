declare module '@divi/module' {
  export const ModuleContainer: any;
  export const StyleContainer: any;
}

declare module '@divi/module-library' {
  export const registerModule: (metadata: any, definition: any) => void;
}

declare module '*.scss';

declare module '*.json' {
  const value: any;
  export default value;
}
