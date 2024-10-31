export interface Root {
    slug: string
    scope: string
    code: string
    name: string
    component: string
    icon: string[]
    blueprint: Blueprint[]
    data: Data
  }
  
  export interface Blueprint {
    name: string
    key: string
    type: string
    icon: string
    blueprint: Blueprint2[]
  }
  
  export interface Blueprint2 {
    name: string
    key: any
    type: any
  }
  
  export interface Data {
    fieldValue: FieldValue
  }
  
  export interface FieldValue {
    column: Column
    PaymentData: PaymentData
    socialData: SocialDaum[]
    copyRight: string
    container: Container
  }
  
  export interface Column {
    column_1: Column1
    column_2: Column2
    column_3: Column3
    column_4: Column4
  }
  
  export interface Column1 {
    name: string
    key: string
    data: Daum[]
  }
  
  export interface Daum {
    name: string
    id: number
    data: Daum2[]
  }
  
  export interface Daum2 {
    name: string
    id: number
  }
  
  export interface Column2 {
    name: string
    key: string
    data: Daum3[]
  }
  
  export interface Daum3 {
    name: string
    id: number
    data: Daum4[]
  }
  
  export interface Daum4 {
    name: string
    id: number
  }
  
  export interface Column3 {
    name: string
    key: string
    data: Daum5[]
  }
  
  export interface Daum5 {
    name: string
    id: number
    data: Daum6[]
  }
  
  export interface Daum6 {
    name: string
    id: number
  }
  
  export interface Column4 {
    name: string
    key: string
    data: Data2
  }
  
  export interface Data2 {
    textBox1: string
    textBox2: string
    textBox3: string
  }
  
  export interface PaymentData {
    data: Daum7[]
  }
  
  export interface Daum7 {
    name: string
    value: string
    image: string
  }
  
  export interface SocialDaum {
    label: string
    icon: any
    link: string
  }
  
  export interface Container {
    properties: Properties
  }
  
  export interface Properties {
    text: Text
    background: Background
    padding: Padding
    margin: Margin
    border: Border
  }
  
  export interface Text {
    color: string
    fontFamily: any
  }
  
  export interface Background {
    type: string
    color: string
    image: Image
  }
  
  export interface Image {
    original: any
  }
  
  export interface Padding {
    unit: string
    top: Top
    left: Left
    right: Right
    bottom: Bottom
  }
  
  export interface Top {
    value: number
  }
  
  export interface Left {
    value: number
  }
  
  export interface Right {
    value: number
  }
  
  export interface Bottom {
    value: number
  }
  
  export interface Margin {
    unit: string
    top: Top2
    left: Left2
    right: Right2
    bottom: Bottom2
  }
  
  export interface Top2 {
    value: number
  }
  
  export interface Left2 {
    value: number
  }
  
  export interface Right2 {
    value: number
  }
  
  export interface Bottom2 {
    value: number
  }
  
  export interface Border {
    color: string
    unit: string
    rounded: Rounded
    top: Top3
    left: Left3
    right: Right3
    bottom: Bottom3
  }
  
  export interface Rounded {
    unit: string
    topright: Topright
    topleft: Topleft
    bottomright: Bottomright
    bottomleft: Bottomleft
  }
  
  export interface Topright {
    value: number
  }
  
  export interface Topleft {
    value: number
  }
  
  export interface Bottomright {
    value: number
  }
  
  export interface Bottomleft {
    value: number
  }
  
  export interface Top3 {
    value: number
  }
  
  export interface Left3 {
    value: number
  }
  
  export interface Right3 {
    value: number
  }
  
  export interface Bottom3 {
    value: number
  }
  