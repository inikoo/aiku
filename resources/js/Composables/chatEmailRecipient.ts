export const chatEmailRecipient = (metadata: Record<string, any> | null | undefined): { address: string; name: string | null } => {
    const repliesToSender = !!metadata?.email_reply_to
    const address = String(metadata?.email_reply_to || metadata?.email_from || "").toLowerCase()
    const name = (repliesToSender ? metadata?.email_reply_to_name : metadata?.email_from_name) || null

    return { address, name }
}

export const chatEmailRecipientLabel = (metadata: Record<string, any> | null | undefined): string => {
    const { address, name } = chatEmailRecipient(metadata)

    return name && address ? `${name} <${address}>` : address
}
