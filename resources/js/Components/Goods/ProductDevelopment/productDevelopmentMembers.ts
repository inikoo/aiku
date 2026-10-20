/*
 * Author Louis Perez
 * Created on 17-10-2026-18h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

export interface MemberUserOption {
    value: number
    label: string
    avatar?: Record<string, string> | null
    agent_ids?: number[]
}

/**
 * The people who can take a role: the agent role goes only to staff of the development's agent,
 * every other role to anyone.
 */
export function peopleForRole<T extends MemberUserOption>(users: T[], role: string | null, agentId: number | null): T[] {
    if (role !== "agent") {
        return users
    }
    if (!agentId) {
        return []
    }

    return users.filter((user) => (user.agent_ids ?? []).includes(agentId))
}
