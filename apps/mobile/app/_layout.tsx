import { Stack } from "expo-router";

// Users-only app. No back-office or super-admin code is shipped in the mobile
// build — those routes do not exist here (§9, §11).
export default function RootLayout() {
  return <Stack />;
}
