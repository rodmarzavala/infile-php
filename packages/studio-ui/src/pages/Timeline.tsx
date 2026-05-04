import { useEffect, useState } from 'react';
import { Clock, CheckCircle2, XCircle, FileText, FileCode2, Copy, RotateCcw, Receipt } from 'lucide-react';
import { translateInfileError } from '../utils/ErrorTranslator';
import { useNavigate } from 'react-router-dom';
import { ReceiptPreview } from '../components/ReceiptPreview';
import { getApiUrl, getApiHeaders } from '../utils/api';

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
  const [copiedTestId, setCopiedTestId] = useState<number | null>(null);
  const [openReceiptId, setOpenReceiptId] = useState<number | null>(null);
  const navigate = useNavigate();

  useEffect(() => {
    fetch(getApiUrl('/timeline'), { headers: getApiHeaders() })
      .then(res => res.json())
      .then(res => {
        setTransactions(res.data || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  const generateTestCode = (tx: Transaction) => {
    let code = `use InfilePhp\\Laravel\\Facades\\FelFake;\nuse InfilePhp\\Core\\Enums\\DteType;\n\n`;
    code += `FelFake::assertIssued(1);\n`;
    
    if (tx.dte_type) {
        const typeConstant = tx.dte_type === 'FACT' ? 'INVOICE' : 
                             tx.dte_type === 'FPEQ' ? 'SMALL_TAXPAYER_INVOICE' : 
                             tx.dte_type === 'NCRE' ? 'CREDIT_NOTE' : tx.dte_type;
        code += `FelFake::assertType(DteType::${typeConstant});\n`;
    }
    if (tx.recipient_tax_id) {
        code += `FelFake::assertRecipient('${tx.recipient_tax_id}');\n`;
    }
    
    return code;
  };

  const handleCopyTest = (tx: Transaction) => {
    const code = generateTestCode(tx);
    navigator.clipboard.writeText(code);
    setCopiedTestId(tx.id);
    setTimeout(() => setCopiedTestId(null), 2000);
  };

  const handleReplay = (tx: Transaction) => {
    // Save to local storage for the builder to pick up
    localStorage.setItem('fel_studio_replay_tx', JSON.stringify({
        dteType: tx.dte_type,
        recipientTaxId: tx.recipient_tax_id,
        // we could also save more details if we captured the full request
    }));
    navigate('/builder');
  };

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
            <div key={tx.id} className="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col items-stretch gap-4">
              <div className="flex items-start gap-4">
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
                      <p className="font-mono text-xs mb-1">Raw Error:</p>
                      <p>{tx.error_message}</p>
                      {translateInfileError(tx.error_message) && (
                        <div className="mt-2 pt-2 border-t border-red-200">
                          <span className="font-semibold">Sugerencia:</span> {translateInfileError(tx.error_message)}
                        </div>
                      )}
                    </div>
                  )}
                  
                  <div className="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    {tx.status === 'issued' && (
                      <>
                        <span className="text-xs text-slate-500 flex items-center gap-1"><FileCode2 className="w-3.5 h-3.5"/> Acciones</span>
                        <div className="flex items-center gap-2">
                          <button 
                            onClick={() => setOpenReceiptId(openReceiptId === tx.id ? null : tx.id)}
                            className="text-xs flex items-center gap-1.5 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded transition-colors"
                          >
                            <Receipt className="w-3.5 h-3.5" /> {openReceiptId === tx.id ? 'Ocultar Recibo' : 'Ver Recibo'}
                          </button>
                          <button 
                            onClick={() => handleCopyTest(tx)}
                            className="text-xs flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition-colors"
                          >
                            {copiedTestId === tx.id ? (
                              <><CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" /> Copiado</>
                            ) : (
                              <><Copy className="w-3.5 h-3.5" /> Generar Test</>
                            )}
                          </button>
                        </div>
                      </>
                    )}

                    {tx.status === 'failed' && (
                      <>
                        <span className="text-xs text-slate-500 flex items-center gap-1"><RotateCcw className="w-3.5 h-3.5"/> Recuperación</span>
                        <button 
                          onClick={() => handleReplay(tx)}
                          className="text-xs flex items-center gap-1.5 px-2.5 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 font-medium rounded transition-colors shadow-sm border border-amber-200"
                        >
                          <RotateCcw className="w-3.5 h-3.5" /> Reintentar en Builder
                        </button>
                      </>
                    )}
                  </div>
                </div>
              </div>
              
              {openReceiptId === tx.id && (
                <div className="border-t border-slate-100 pt-4 mt-2">
                  {tx.payload?.xml_certified ? (
                    <ReceiptPreview xmlBase64={tx.payload.xml_certified} />
                  ) : (
                    <div className="text-sm text-slate-500 bg-slate-50 p-4 rounded-lg text-center border border-slate-200 border-dashed">
                      Esta transacción se emitió antes de la actualización del Generador de Recibos y no contiene el XML guardado. 
                      Por favor, emite una <strong>nueva factura</strong> para ver el recibo generado.
                    </div>
                  )}
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
