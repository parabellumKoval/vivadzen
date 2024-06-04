export type Delivery = {
  method: 'warehouse' | 'address' | 'pickup',
  // Settlement name
  settlement: string | null,
  settlementRef: string | null,
  // Settlement region name
  region: string | null,
  // Settlement type name
  type: string | null,
  // Settlement area name
  area: string | null,
  // Street name
  street: string | null,
  streetRef: string | null,
  house: string | null,
  room: string | null,
  zip: number | null,
  // Full warehouse description
  warehouse: string | null,
  warehouseRef: string | null,
  comment: string | null
}