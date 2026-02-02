import { TestViewer } from "@/app/components/TestViewer";

export default function TestPage() {
  return (
    <div style={{ padding: "20px" }}>
      <h1>Test 3D Viewer</h1>
      <p>If you see a rotating green cube below, Three.js is working:</p>
      <TestViewer />
      <hr style={{ margin: "40px 0" }} />
      <h2>Console Check</h2>
      <p>Open DevTools (F12) and look for "TestViewer:" messages in the console</p>
    </div>
  );
}
