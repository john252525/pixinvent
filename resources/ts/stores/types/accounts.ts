export const accountSources = ['whatsapp', 'telegram', 'crm', 'sms', 'helpdesk']
export type Source = typeof accountSources[number]
export interface AccountClient {
  step: Step | null;
  state: boolean;
  webhookUrls: string[];
  activated: boolean;
  defaultState: boolean;
  apiV: string;
  additional: Additional;
  _id: string;
  owner: string;
  login: string;
  proxyString: string;
  addedTime: number;
  phone_code?: string;
  qr_code?: string;
  __v: number;
}

export interface Step {
  message: string;
  value: number| null;
}

export interface Additional {
  isBeta: boolean;
  _id: string;
  config: Config;
}

export interface Config {
  _id: string;
  authPhone?: string;
  services: Services;
  conflict: Conflict;
}

export interface Services {
  outgoingOnlyHook: boolean;
  authMethod: string;
  _id: string;
  stateHook: boolean;
}

export interface Conflict {
  _id: string;
  takeover: boolean;
  takeoverTimeoutMs: number;
}

export interface Summary {
  active: number;
  activated: number;
  demo: number;
  count: number;
  payment: Payment;
}

export interface Payment {
  mode: string;
  balance: number;
}

export interface ApiResponse {
  clients: TelegramClient[] | WhatsappClient[];
  summary: Summary;
  errors: string[];
  status: string;
  uuid: string;
}

// WhatsApp specific properties
export interface WhatsappClient extends AccountClient {
  step: Step | null;
  state: boolean;
  webhookUrls: string[];
  activated: boolean;
  defaultState: boolean;
  apiV: string;
  additional: Additional;
  _id: string;
  owner: string;
  login: string;
  proxyString: string;
  addedTime: number;
  __v: number;
}
// Telegram specific properties
export interface TelegramClient extends AccountClient {
  owner: string;
  login: string;
  proxyString: string;
  webhookUrl: string | null;
  webhookUrls: string[];
  additional: Additional;
  createdAt: number;
  step: Step | null;
  state: boolean;
}
