import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Layout from './Layout';

import { useEffect, useState } from 'react';
import { CheckCircle, AlertCircle } from 'lucide-react';
import { getApiUrl, getApiHeaders } from './utils/api';

function Dashboard() {
  const [health, setHealth] = useState<any>(null);

  useEffect(() => {
    fetch(getApiUrl('/health'), { headers: getApiHeaders() })
      .then(res => res.json())
      .then(data => setHealth(data))
      .catch(console.error);
  }, []);

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold tracking-tight text-slate-900">Dashboard</h2>
      <div className="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl">
        <h3 className="font-semibold mb-1">Bienvenido a Infile FEL Studio</h3>
        <p className="text-sm">Esta herramienta te permite diseñar, previsualizar y validar documentos electrónicos (DTE) de Guatemala localmente antes de integrarlos a tu código. Navega por el Visual Builder para crear facturas o el Timeline para ver el registro de peticiones.</p>
      </div>
      
      {health && (
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
          <h3 className="text-lg font-semibold text-slate-900">Health Check & Credentials</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-slate-600">Conexión a Infile ({health.connection.environment})</span>
                {health.connection.success ? (
                  <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                    <CheckCircle className="w-4 h-4" /> OK ({health.connection.latency_ms}ms)
                  </span>
                ) : (
                  <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <AlertCircle className="w-4 h-4" /> Falla
                  </span>
                )}
              </div>
              {health.connection.error && (
                <div className="text-xs text-red-600 bg-red-50 p-2 rounded">
                  {health.connection.error}
                </div>
              )}
            </div>
            
            <div className="space-y-2 border-t md:border-t-0 md:border-l md:pl-4 border-slate-100 pt-4 md:pt-0">
              <h4 className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Estado de .env</h4>
              <ul className="space-y-2 text-sm">
                {Object.entries(health.env).map(([key, valid]) => (
                  <li key={key} className="flex items-center justify-between">
                    <span className="font-mono text-slate-600">FEL_{key.toUpperCase()}</span>
                    {valid ? (
                      <CheckCircle className="w-4 h-4 text-emerald-500" />
                    ) : (
                      <AlertCircle className="w-4 h-4 text-red-500" />
                    )}
                  </li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
          <h3 className="text-sm font-medium text-slate-500">DTEs Emitidos (Local)</h3>
          <p className="text-3xl font-bold mt-2">0</p>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
          <h3 className="text-sm font-medium text-slate-500">Tasa de Éxito</h3>
          <p className="text-3xl font-bold text-emerald-600 mt-2">100%</p>
        </div>
        <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
          <h3 className="text-sm font-medium text-slate-500">Tiempo Promedio</h3>
          <p className="text-3xl font-bold mt-2">
             {health ? `${health.connection.latency_ms}ms` : '0ms'}
          </p>
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
