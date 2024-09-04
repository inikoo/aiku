export interface stockLocation {
    id: number;
    slug: string;
    code: string;
    unit_value?: any;
    description?: any;
    number_locations: number;
    quantity_locations: string;
    photo?: any;
    locations: Locations;
  }
  interface Locations {
    data: Datum[];
  }
  export interface Datum {
    id: number;
    quantity: number;
    value: string;
    audited_at: string;
    commercial_value: string;
    type: string;
    picking_priority: number;
    notes?: any;
    data: any[];
    settings: Settings;
    created_at: string;
    updated_at: string;
    location: Location;
  }
  interface Location {
    id: number;
    slug: string;
    code: string;
    tags: any[];
    allow_stocks: boolean;
    allow_fulfilment: boolean;
    allow_dropshipping: boolean;
    has_stock_slots: boolean;
    has_fulfilment: boolean;
    has_dropshipping_slots: boolean;
  }
  interface Settings {
    max_stock: number;
    min_stock: number;
  }