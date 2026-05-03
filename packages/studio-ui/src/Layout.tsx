import { Outlet, NavLink } from 'react-router-dom';
import { Activity, Clock, FileText, Settings, Database } from 'lucide-react';

export default function Layout() {
  const navItems = [
    { to: '/', icon: Activity, label: 'Dashboard' },
    { to: '/timeline', icon: Clock, label: 'Timeline' },
    { to: '/builder', icon: FileText, label: 'Visual Builder' },
    { to: '/interceptor', icon: Database, label: 'Interceptor' },
  ];

  return (
    <div className="flex h-screen bg-slate-50 text-slate-900">
      {/* Sidebar */}
      <div className="w-64 bg-slate-900 text-slate-50 flex flex-col">
        <div className="p-6">
          <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
            <span className="text-blue-500">FEL</span> Studio
          </h1>
          <p className="text-slate-400 text-xs mt-1">Local Development UI</p>
        </div>
        
        <nav className="flex-1 px-4 space-y-2 mt-4">
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${
                  isActive 
                    ? 'bg-blue-600 text-white' 
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                }`
              }
            >
              <item.icon className="w-5 h-5" />
              <span className="font-medium">{item.label}</span>
            </NavLink>
          ))}
        </nav>
        
        <div className="p-4 border-t border-slate-800">
          <button className="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-white w-full rounded-lg transition-colors">
            <Settings className="w-5 h-5" />
            <span className="font-medium">Settings</span>
          </button>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        <header className="h-16 border-b border-slate-200 bg-white flex items-center px-8 shadow-sm z-10">
          <div className="flex items-center gap-2">
            <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Infile API Connected
            </span>
          </div>
        </header>
        <main className="flex-1 overflow-auto p-8 bg-slate-50">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
