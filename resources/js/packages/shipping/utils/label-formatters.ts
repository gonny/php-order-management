import type { LabelStatus, ShippingLabel } from '../types';

// Label status utilities
export function getLabelStatusColor(status: LabelStatus): 'default' | 'secondary' | 'destructive' | 'outline' {
  const statusColorMap: Record<LabelStatus, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    generated: 'default',
    printed: 'secondary',
    voided: 'destructive',
    error: 'destructive',
  };
  
  return statusColorMap[status];
}

export function getLabelStatusLabel(status: LabelStatus): string {
  const statusLabelMap: Record<LabelStatus, string> = {
    pending: 'Pending',
    generated: 'Generated',
    printed: 'Printed',
    voided: 'Voided',
    error: 'Error',
  };
  
  return statusLabelMap[status];
}

export function isLabelActionable(label: ShippingLabel): boolean {
  return ['generated', 'printed'].includes(label.status);
}

export function canVoidLabel(label: ShippingLabel): boolean {
  return ['generated', 'printed'].includes(label.status);
}

export function canDownloadLabel(label: ShippingLabel): boolean {
  return label.status === 'generated' && Boolean(label.label_url || label.file_path);
}

export function formatLabelId(labelId: string): string {
  return `#${labelId.slice(-8)}`;
}

export function formatTrackingNumber(trackingNumber?: string): string {
  if (!trackingNumber) return 'N/A';
  
  // Format tracking number for better readability
  if (trackingNumber.length > 10) {
    return trackingNumber.replace(/(.{4})/g, '$1 ').trim();
  }
  
  return trackingNumber;
}