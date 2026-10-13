/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"

const serpFeatureLabels = (): Record<string, string> => ({
    ai_overview: ctrans("AI Overview"),
    featured_snippet: ctrans("Featured snippet"),
    local_pack: ctrans("Local pack"),
    map: ctrans("Map"),
    shopping: ctrans("Shopping results"),
    popular_products: ctrans("Popular products"),
    paid: ctrans("Ads"),
    people_also_ask: ctrans("People also ask"),
    people_also_search: ctrans("People also search"),
    related_searches: ctrans("Related searches"),
    images: ctrans("Images"),
    video: ctrans("Videos"),
    top_stories: ctrans("Top stories"),
    knowledge_graph: ctrans("Knowledge panel"),
    discussions_and_forums: ctrans("Forums"),
})

export const featureLabel = (feature: string): string => serpFeatureLabels()[feature] ?? capitalize(feature.replaceAll("_", " "))
