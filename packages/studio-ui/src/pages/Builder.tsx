import { useState } from 'react';
import { Play, CheckCircle, AlertCircle, Plus, Trash2 } from 'lucide-react';

export default function Builder() {
  const [recipient, setRecipient] = useState({
    tax_id: 'CF',
    name: 'Ciudadano',
    address: 'Ciudad',
  });

  const [items, setItems] = useState([
    { type: 'B', description: 'Item 1', quantity: 1, unit_price: 100 },
  ]);

  const [xmlResult, setXmlResult] = useState('');
  const [status, setStatus] = useState<{ type: 'idle' | 'success' | 'error'; message: string }>({
    type: 'idle',
    message: '',
  });
  const [loading, setLoading] = useState(false);

  const addItem = () => {
    setItems([...items, { type: 'B', description: '', quantity: 1, unit_price: 0 }]);
  };

  const removeItem = (index: number) => {
    setItems(items.filter((_, i) => i !== index));
  };

  const updateItem = (index: number, field: string, value: any) => {
    const newItems = [...items];
    newItems[index] = { ...newItems[index], [field]: value };
    setItems(newItems);
  };

  const buildPayload = () => ({
    recipient,
    items: items.map(item => ({
      ...item,
      quantity: Number(item.quantity),
      unit_price: Number(item.unit_price),
    })),
  });

  const handlePreview = async () => {
    setLoading(true);
    setStatus({ type: 'idle', message: '' });
    
    try {
      const response = await fetch('/fel-studio/api/builder/preview', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(buildPayload()),
      });
      
      const data = await response.json();
      
      if (data.success) {
        setXmlResult(data.xml);
        setStatus({ type: 'success', message: 'Preview generated successfully.' });
      } else {
        setStatus({ type: 'error', message: data.error || 'Unknown error' });
      }
    } catch (err: any) {
      setStatus({ type: 'error', message: err.message });
    } finally {
      setLoading(false);
    }
  };

  const handleValidate = async () => {
    setLoading(true);
    setStatus({ type: 'idle', message: '' });
    
    try {
      const response = await fetch('/fel-studio/api/builder/validate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(buildPayload()),
      });
      
      const data = await response.json();
      
      if (data.success) {
        setStatus({ type: 'success', message: data.message || 'Validation passed!' });
      } else {
        setStatus({ type: 'error', message: data.error || 'Validation failed' });
      }
    } catch (err: any) {
      setStatus({ type: 'error', message: err.message });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h2 className="text-3xl font-bold tracking-tight text-slate-900">Visual DTE Builder</h2>
        <div className="flex items-center gap-3">
          <button
            onClick={handlePreview}
            disabled={loading}
            className="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors disabled:opacity-50"
          >
            <Play className="w-4 h-4" />
            Preview XML
          </button>
          <button
            onClick={handleValidate}
            disabled={loading}
            className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50 shadow-sm shadow-indigo-200"
          >
            <CheckCircle className="w-4 h-4" />
            Validate Structure
          </button>
        </div>
      </div>

      {status.type !== 'idle' && (
        <div className={`p-4 rounded-xl border flex items-start gap-3 ${
          status.type === 'success' 
            ? 'bg-emerald-50 border-emerald-200 text-emerald-800' 
            : 'bg-red-50 border-red-200 text-red-800'
        }`}>
          {status.type === 'success' ? (
            <CheckCircle className="w-5 h-5 shrink-0" />
          ) : (
            <AlertCircle className="w-5 h-5 shrink-0" />
          )}
          <p className="text-sm font-medium">{status.message}</p>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Editor form */}
        <div className="space-y-6">
          {/* Recipient Section */}
          <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 className="text-lg font-semibold text-slate-900 mb-4">Recipient</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">NIT / Tax ID</label>
                <input
                  type="text"
                  value={recipient.tax_id}
                  onChange={(e) => setRecipient({ ...recipient, tax_id: e.target.value })}
                  className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Name</label>
                  <input
                    type="text"
                    value={recipient.name}
                    onChange={(e) => setRecipient({ ...recipient, name: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Address</label>
                  <input
                    type="text"
                    value={recipient.address}
                    onChange={(e) => setRecipient({ ...recipient, address: e.target.value })}
                    className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Items Section */}
          <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-slate-900">Line Items</h3>
              <button
                onClick={addItem}
                className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors"
              >
                <Plus className="w-4 h-4" />
                Add Item
              </button>
            </div>
            
            <div className="space-y-4">
              {items.map((item, index) => (
                <div key={index} className="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-3 relative group">
                  <button 
                    onClick={() => removeItem(index)}
                    className="absolute -top-2 -right-2 p-1.5 bg-red-100 text-red-600 rounded-full opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-200"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                  
                  <div className="grid grid-cols-[80px_1fr] gap-3">
                    <div>
                      <label className="block text-xs font-medium text-slate-500 mb-1">Type</label>
                      <select
                        value={item.type}
                        onChange={(e) => updateItem(index, 'type', e.target.value)}
                        className="w-full px-2 py-1.5 border border-slate-300 rounded-md text-sm bg-white"
                      >
                        <option value="B">Bien</option>
                        <option value="S">Servicio</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-xs font-medium text-slate-500 mb-1">Description</label>
                      <input
                        type="text"
                        value={item.description}
                        onChange={(e) => updateItem(index, 'description', e.target.value)}
                        className="w-full px-2 py-1.5 border border-slate-300 rounded-md text-sm"
                        placeholder="Description..."
                      />
                    </div>
                  </div>
                  
                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <label className="block text-xs font-medium text-slate-500 mb-1">Quantity</label>
                      <input
                        type="number"
                        min="0.01"
                        step="0.01"
                        value={item.quantity}
                        onChange={(e) => updateItem(index, 'quantity', e.target.value)}
                        className="w-full px-2 py-1.5 border border-slate-300 rounded-md text-sm"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-medium text-slate-500 mb-1">Unit Price (Q)</label>
                      <input
                        type="number"
                        min="0"
                        step="0.01"
                        value={item.unit_price}
                        onChange={(e) => updateItem(index, 'unit_price', e.target.value)}
                        className="w-full px-2 py-1.5 border border-slate-300 rounded-md text-sm"
                      />
                    </div>
                  </div>
                </div>
              ))}
              
              {items.length === 0 && (
                <div className="text-center py-6 text-sm text-slate-500 italic bg-slate-50 rounded-lg border border-slate-200 border-dashed">
                  No items added yet.
                </div>
              )}
            </div>
          </div>
        </div>

        {/* XML Result */}
        <div className="bg-slate-900 rounded-xl shadow-sm overflow-hidden flex flex-col h-[600px] lg:h-auto">
          <div className="px-4 py-3 bg-slate-800 border-b border-slate-700 flex items-center justify-between">
            <h3 className="text-sm font-medium text-slate-200 font-mono">Output XML</h3>
          </div>
          <div className="p-4 overflow-auto flex-1 text-slate-300 text-sm font-mono whitespace-pre text-left">
            {xmlResult || (
              <div className="flex h-full items-center justify-center text-slate-600 italic">
                Click "Preview XML" to generate the document
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
