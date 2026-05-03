import { useEffect, useState } from 'react';
import { Clock, CheckCircle2, XCircle, FileText } from 'lucide-react';

type Transaction = {
  id: number;
  uuid: string | null;
  serie: string | null;
  numero: string | null;
  dte_type: string;
  recipient_tax_id: string;
  idempotency_key: string;
  status: string;
  payload: any;
  error_message: string | null;
  created_at: string;
};

export default function Timeline() {
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/fel-studio/api/timeline')
      .then(res => res.json())
      .then(res => {
        setTransactions(res.data || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  if (loading) {
    return <div className="p-8 text-center text-slate-500">Loading timeline...</div>;
  }

  return (
    <div className="space-y-6">
      <h2 className="text-3xl font-bold tracking-tight text-slate-900">Timeline</h2>
      
      {transactions.length === 0 ? (
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-8 text-center text-slate-500">
          No transactions recorded yet. Fire a DteIssued event to see it here!
        </div>
      ) : (
        <div className="space-y-4">
          {transactions.map(tx => (
            <div key={tx.id} className="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-start gap-4">
              <div className="pt-1">
                {tx.status === 'issued' ? (
                  <CheckCircle2 className="w-6 h-6 text-emerald-500" />
                ) : tx.status === 'cancelled' ? (
                  <FileText className="w-6 h-6 text-amber-500" />
                ) : (
                  <XCircle className="w-6 h-6 text-red-500" />
                )}
              </div>
              
              <div className="flex-1">
                <div className="flex items-center justify-between mb-1">
                  <h3 className="font-semibold text-slate-900">
                    {tx.dte_type} 
                    <span className="text-slate-400 font-normal ml-2 text-sm">
                      to {tx.recipient_tax_id || 'Unknown'}
                    </span>
                  </h3>
                  <div className="flex items-center gap-1.5 text-xs text-slate-400">
                    <Clock className="w-3.5 h-3.5" />
                    {new Date(tx.created_at).toLocaleString()}
                  </div>
                </div>
                
                {tx.uuid && (
                  <div className="text-sm text-slate-600 mb-2 font-mono">
                    UUID: {tx.uuid} <br/>
                    {tx.serie && tx.numero && `Serie: ${tx.serie} | No: ${tx.numero}`}
                  </div>
                )}

                {tx.error_message && (
                  <div className="mt-2 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-100">
                    {tx.error_message}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
