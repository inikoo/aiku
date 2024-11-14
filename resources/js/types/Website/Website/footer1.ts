export interface Root {
  slug: string
  scope: string
  code: string
  name: string
  component: string
  icon: string[]
  blueprint: any[]
  data: Data
}

export interface Data {
  fieldValue: FieldValue
}

export interface FieldValue {
  columns: Columns
  container: Container
  copyright: string
  socialMedia: SocialMedum[]
  paymentData: PaymentData
  whatsapp: Whatsapp
  phone: Phone
  email: string
  logo: Logo
}

export interface Columns {
  column_1: Column1
  column_2: Column2
  column_3: Column3
  column_4: Column4
}

export interface Column1 {
  types: string
  key: string
  data: Daum[]
  name: string
}

export interface Daum {
  id: number
  data: Daum2[]
  name: string
}

export interface Daum2 {
  id: number
  name: string
}

export interface Column2 {
  types: string
  key: string
  data: Daum3[]
  name: string
}

export interface Daum3 {
  id: number
  data: Daum4[]
  name: string
}

export interface Daum4 {
  id: number
  name: string
}

export interface Column3 {
  types: string
  key: string
  data: Daum5[]
  name: string
}

export interface Daum5 {
  id: number
  data: Daum6[]
  name: string
}

export interface Daum6 {
  id: number
  name: string
}

export interface Column4 {
  types: string
  key: string
  data: Data2
  name: string
}

export interface Data2 {
  textBox1: string
  textBox2: string
  textBox3: string
}

export interface Container {
  properties: Properties
}

export interface Properties {
  text: Text
  background: Background
}

export interface Text {
  fontFamily: string
}

export interface Background {
  type: string
  color: string
  image: Image
}

export interface Image {
  original: any
}

export interface SocialMedum {
  icon: string
  link: string
  type: string
}

export interface PaymentData {
  data: Daum7[]
}

export interface Daum7 {
  name: string
  image: string
  value: string
}

export interface Whatsapp {
  number: string
  caption: string
  message: string
}

export interface Phone {
  numbers: string[]
  caption: string
}

export interface Logo {
  source: string
  alt: string
}
