import {Additional, Step} from "@/stores/types/accounts";

export interface MailingClient {
  id: number;
  owner: string;
  proxyString: string;
  webhookUrl: string | null;
  webhookUrls: string[];
  additional: Additional;
  createdAt: number;
  step: Step | null;
  state: boolean;
}

export interface Step {
  value: number| null;
  message: string;
}
