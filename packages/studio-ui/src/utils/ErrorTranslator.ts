export function translateInfileError(rawError: string | null): string | null {
  if (!rawError) return null;

  const lowerError = rawError.toLowerCase();

  // Known SAT / Infile errors mapping
  if (lowerError.includes('no afiliado') || lowerError.includes('no esta afiliado')) {
    return "El NIT ingresado no está afiliado al régimen de Factura Electrónica en Línea (FEL) en la Agencia Virtual de SAT, o el NIT es inválido. Asegúrate de activarlo antes de emitir.";
  }

  if (lowerError.includes('namespace v2') || lowerError.includes('esquema')) {
    return "Error de validación XSD. La estructura del XML generado no cumple con la regla de SAT. Revisa los montos, impuestos y tipos de DTE.";
  }

  if (lowerError.includes('credenciales') || lowerError.includes('firma')) {
    return "Las credenciales (UsuarioFirma o LlaveFirma) son incorrectas. Verifica tu archivo .env y asegúrate de estar apuntando al ambiente correcto (Sandbox/Produccion).";
  }

  if (lowerError.includes('api_key') || lowerError.includes('usuarioapi')) {
    return "El UsuarioApi o LlaveApi proveídos por Infile son incorrectos o el token ha expirado.";
  }

  if (lowerError.includes('impuesto') || lowerError.includes('monto')) {
    return "Discrepancia matemática en el XML. La suma de los Items + Impuestos no coincide con los Totales declarados. Verifica los cálculos de precio unitario vs cantidad.";
  }
  
  if (lowerError.includes('timeout') || lowerError.includes('timeout')) {
     return "Tiempo de espera agotado. Los servicios de Infile o SAT podrían estar experimentando lentitud. El SDK entrará en modo contingencia si está habilitado.";
  }

  // If no match, return null to avoid cluttering with incorrect guesses
  return null;
}
