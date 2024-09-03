export interface UserProperties {
  id?: number | undefined
  name: string
  email: string
  email_verified_at?: string | null
  roles?: string[] | undefined
  password?: string | undefined
}
