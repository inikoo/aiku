
export interface TopbarFulfilmentTypes {
    key: string
    login: Login
    logout: Logout
    profile: Profile
    greeting: Greeting
    container: Container4
    main_title: MainTitle
  }
  
  export interface Login {
    link: Link
    text: string
    visible: string
    container: Container
  }
  
  export interface Link {
    href: string
    type: string
    target: string
  }
  
  export interface Container {
    properties: Properties
  }
  
  export interface Properties {
    text: Text
    border: Border
    margin: Margin
    padding: Padding
    background: Background
  }
  
  export interface Text {
    color: string
  }
  
  export interface Border {
    top: Top
    left: Left
    unit: string
    color: string
    right: Right
    bottom: Bottom
    rounded: Rounded
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
  
  export interface Rounded {
    unit: string
    topleft: Topleft
    topright: Topright
    bottomleft: Bottomleft
    bottomright: Bottomright
  }
  
  export interface Topleft {
    value: number
  }
  
  export interface Topright {
    value: number
  }
  
  export interface Bottomleft {
    value: number
  }
  
  export interface Bottomright {
    value: number
  }
  
  export interface Margin {
    top: Top2
    left: Left2
    unit: string
    right: Right2
    bottom: Bottom2
  }
  
  export interface Top2 {
    value: any
  }
  
  export interface Left2 {
    value: any
  }
  
  export interface Right2 {
    value: any
  }
  
  export interface Bottom2 {
    value: any
  }
  
  export interface Padding {
    top: Top3
    left: Left3
    unit: string
    right: Right3
    bottom: Bottom3
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
  
  export interface Background {
    type: string
    color: string
    image: Image
  }
  
  export interface Image {
    original: any
  }
  
  export interface Logout {
    link: any
    text: string
    visible: string
    container: Container2
  }
  
  export interface Container2 {
    properties: Properties2
  }
  
  export interface Properties2 {
    text: Text2
    border: Border2
    margin: any[]
    padding: Padding2
  }
  
  export interface Text2 {
    color: string
    fontFamily: string
  }
  
  export interface Border2 {
    top: Top4
    left: Left4
    unit: string
    color: any
    right: Right4
    bottom: Bottom4
    rounded: Rounded2
  }
  
  export interface Top4 {
    value: number
  }
  
  export interface Left4 {
    value: number
  }
  
  export interface Right4 {
    value: number
  }
  
  export interface Bottom4 {
    value: number
  }
  
  export interface Rounded2 {
    unit: string
    topleft: Topleft2
    topright: Topright2
    bottomleft: Bottomleft2
    bottomright: Bottomright2
  }
  
  export interface Topleft2 {
    value: number
  }
  
  export interface Topright2 {
    value: number
  }
  
  export interface Bottomleft2 {
    value: number
  }
  
  export interface Bottomright2 {
    value: number
  }
  
  export interface Padding2 {
    top: Top5
    left: Left5
    unit: string
    right: Right5
    bottom: Bottom5
  }
  
  export interface Top5 {
    value: number
  }
  
  export interface Left5 {
    value: number
  }
  
  export interface Right5 {
    value: number
  }
  
  export interface Bottom5 {
    value: number
  }
  
  export interface Profile {
    link: any
    text: string
    visible: string
    container: Container3
  }
  
  export interface Container3 {
    properties: Properties3
  }
  
  export interface Properties3 {
    text: Text3
    padding: Padding3
  }
  
  export interface Text3 {
    color: string
  }
  
  export interface Padding3 {
    top: Top6
    left: Left6
    unit: string
    right: Right6
    bottom: Bottom6
  }
  
  export interface Top6 {
    value: number
  }
  
  export interface Left6 {
    value: number
  }
  
  export interface Right6 {
    value: number
  }
  
  export interface Bottom6 {
    value: number
  }
  
  export interface Greeting {
    text: string
    visible: string
  }
  
  export interface Container4 {
    properties: Properties4
  }
  
  export interface Properties4 {
    text: Text4
    background: Background2
  }
  
  export interface Text4 {
    color: string
    fontFamily: string
  }
  
  export interface Background2 {
    type: string
    color: string
    image: Image2
  }
  
  export interface Image2 {
    original: any
  }
  
  export interface MainTitle {
    text: string
    visible: string
  }
  