import { createHead } from '@unhead/vue'

const head = createHead()


export default function (app: any) {
  app.use(head)
}
