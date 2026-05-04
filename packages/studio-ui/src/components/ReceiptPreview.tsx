import React, { useMemo } from 'react';

interface ReceiptPreviewProps {
    xmlBase64: string;
}

export const ReceiptPreview: React.FC<ReceiptPreviewProps> = ({ xmlBase64 }) => {
    const data = useMemo(() => {
        try {
            const decoded = atob(xmlBase64);
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(decoded, 'text/xml');

            // Helper to get attribute from a namespace-agnostic tag
            const getAttr = (tagName: string, attr: string) => {
                const el = xmlDoc.getElementsByTagNameNS('*', tagName)[0] || xmlDoc.getElementsByTagName(tagName)[0];
                return el ? el.getAttribute(attr) || '' : '';
            };

            const emisorNombre = getAttr('Emisor', 'NombreEmisor');
            const emisorNit = getAttr('Emisor', 'NITEmisor');

            const receptorNombre = getAttr('Receptor', 'NombreReceptor');
            const receptorNit = getAttr('Receptor', 'IDReceptor');

            const itemsNodes = xmlDoc.getElementsByTagNameNS('*', 'Item');
            const itemsNodesFallback = itemsNodes.length ? itemsNodes : xmlDoc.getElementsByTagName('Item');
            
            const items = Array.from(itemsNodesFallback).map((item) => {
                return {
                    cantidad: item.getAttribute('Cantidad') || '1',
                    descripcion: item.getAttribute('Descripcion') || '',
                    precioUnitario: item.getAttribute('PrecioUnitario') || '0.00',
                    total: item.getAttribute('Total') || '0.00',
                };
            });

            const total = getAttr('GranTotal', 'Valor');

            const dteNode = xmlDoc.getElementsByTagNameNS('*', 'DatosEmision')[0] || xmlDoc.getElementsByTagName('DatosEmision')[0];
            const fecha = dteNode ? dteNode.getAttribute('FechaHoraEmision') || '' : '';
            
            // Extract UUID from certification
            const certNode = xmlDoc.getElementsByTagNameNS('*', 'Certificacion')[0] || xmlDoc.getElementsByTagName('Certificacion')[0];
            const autorizacionNode = certNode?.getElementsByTagNameNS('*', 'NumeroAutorizacion')[0] || certNode?.getElementsByTagName('NumeroAutorizacion')[0];
            const uuid = autorizacionNode ? autorizacionNode.textContent || autorizacionNode.getAttribute('Numero') || '' : '';

            return {
                emisorNombre, emisorNit,
                receptorNombre, receptorNit,
                items, total, fecha, uuid
            };
        } catch (e) {
            console.error('Failed to parse XML', e);
            return null;
        }
    }, [xmlBase64]);

    if (!data) {
        return <div className="text-red-500 text-sm">Error al leer el XML certificado.</div>;
    }

    const copyHtml = () => {
        const html = document.getElementById('receipt-container')?.innerHTML || '';
        navigator.clipboard.writeText(`<div style="font-family: monospace; max-width: 300px;">${html}</div>`);
        alert('HTML Copiado al portapapeles');
    };

    return (
        <div className="flex flex-col items-center mt-4">
            <div 
                id="receipt-container"
                className="bg-white text-gray-900 font-mono text-xs p-6 shadow-lg border border-gray-200" 
                style={{ width: '300px' }}
            >
                <div className="text-center font-bold text-sm mb-2">{data.emisorNombre}</div>
                <div className="text-center mb-4">NIT: {data.emisorNit}</div>
                
                <div className="border-b border-dashed border-gray-400 mb-4"></div>
                
                <div className="mb-1"><strong>Fecha:</strong> {data.fecha}</div>
                <div className="mb-1"><strong>Autorización:</strong> {data.uuid}</div>
                <div className="mb-1 mt-4"><strong>Cliente:</strong> {data.receptorNombre}</div>
                <div className="mb-4"><strong>NIT:</strong> {data.receptorNit}</div>

                <div className="border-b border-dashed border-gray-400 mb-4"></div>

                <table className="w-full mb-4">
                    <thead>
                        <tr className="text-left border-b border-gray-300">
                            <th className="font-normal w-8 pb-1">CANT</th>
                            <th className="font-normal pb-1">DESC</th>
                            <th className="font-normal text-right pb-1">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        {data.items.map((item, idx) => (
                            <tr key={idx}>
                                <td className="py-1 align-top">{item.cantidad}</td>
                                <td className="py-1 align-top break-words pr-2">{item.descripcion}</td>
                                <td className="py-1 align-top text-right">Q{item.total}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                <div className="border-b border-dashed border-gray-400 mb-4"></div>

                <div className="flex justify-between items-center font-bold text-base mb-6">
                    <span>GRAN TOTAL</span>
                    <span>Q{data.total}</span>
                </div>

                <div className="text-center text-[10px] text-gray-500">
                    Sujeto a pagos trimestrales ISR<br />
                    Certificador: INFILE S.A.
                </div>
            </div>

            <button 
                onClick={copyHtml}
                className="mt-4 px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 transition"
            >
                Copiar HTML del Ticket
            </button>
        </div>
    );
};
