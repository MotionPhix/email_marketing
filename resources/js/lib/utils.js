import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs) {
  return twMerge(clsx(inputs));
}

export function excludeKeys (obj, keys) {
  return Object.fromEntries(
    Object.entries(obj).filter(([key]) => !keys.includes(key))
  )
}

// Usage
// const newObject = excludeKeys(originalObject, keysToExclude);
