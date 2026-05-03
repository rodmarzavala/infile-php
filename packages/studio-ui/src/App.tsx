import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Layout from './Layout';

function Dashboard() {
  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold tracking-tight text-slate-900">Dashboard</h2>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
          <h3 className="text-sm font-medium text-slate-500">DTEs Issued (Local)</h3>
          <p className="text-3xl font-bold mt-2">0</p>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
          <h3 className="text-sm font-medium text-slate-500">Success Rate</h3>
          <p className="text-3xl font-bold text-emerald-600 mt-2">100%</p>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
          <h3 className="text-sm font-medium text-slate-500">Avg Certification Time</h3>
          <p className="text-3xl font-bold mt-2">0ms</p>
        </div>
      </div>
    </div>
  );
}

import Builder from './pages/Builder';
import Timeline from './pages/Timeline';
import Interceptor from './pages/Interceptor';

function App() {
  // If we are served from Laravel /fel-studio, we use that basename
  // In dev (Vite), it can just serve from /fel-studio too.
  return (
    <BrowserRouter basename="/fel-studio">
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Dashboard />} />
          <Route path="timeline" element={<Timeline />} />
          <Route path="builder" element={<Builder />} />
          <Route path="interceptor" element={<Interceptor />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App;
