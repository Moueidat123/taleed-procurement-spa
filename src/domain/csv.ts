/** Neutralize spreadsheet formula prefixes; quote every field for RFC-style CSV output. */
export function safeSpreadsheetText(value: unknown): string {
  const text = String(value ?? '');
  // eslint-disable-next-line no-control-regex -- intentional: strip leading control chars to prevent CSV injection
  return /^[\u0000-\u0020]*[=+@-]/.test(text) ? `'${text}` : text;
}
export function toCsv(rows: unknown[][]): string {
  return '\uFEFF' + rows.map((row) => row.map((value) => `"${safeSpreadsheetText(value).replaceAll('"','""')}"`).join(',')).join('\r\n');
}
