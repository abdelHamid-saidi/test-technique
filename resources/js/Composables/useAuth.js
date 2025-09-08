import { computed } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'

/**
 * Composable pour sécuriser l'accès aux données d'authentification
 * @returns {Object} 
 */
export function useAuth() {
  const page = usePage()
  
  // Accès sécurisé aux props d'authentification
  const user = computed(() => page.props.value.auth?.user || null)
  
  // Données utilisateur sécurisées (uniquement celles utilisées)
  const userName = computed(() => user.value?.name || '')
  const userEmail = computed(() => user.value?.email || '')
  const userProfilePhotoUrl = computed(() => user.value?.profile_photo_url || '')
  const userCurrentTeam = computed(() => user.value?.current_team || null)
  const userCurrentTeamId = computed(() => user.value?.current_team_id || null)
  const userAllTeams = computed(() => user.value?.all_teams || [])
  
  return {
    user,
    userName,
    userEmail,
    userProfilePhotoUrl,
    userCurrentTeam,
    userCurrentTeamId,
    userAllTeams,
  }
}
