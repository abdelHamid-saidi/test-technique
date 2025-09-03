import { computed } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'

/**
 * Composable pour sécuriser l'accès aux fonctionnalités Jetstream
 * @returns {Object}
 */
export function useJetstream() {
  const page = usePage()
  
  // Accès sécurisé aux props Jetstream
  const jetstream = computed(() => page.props.value.jetstream || {})
  
  // Fonctionnalités d'équipe (uniquement celles utilisées)
  const hasTeamFeatures = computed(() => !!jetstream.value.hasTeamFeatures)
  const canCreateTeams = computed(() => !!jetstream.value.canCreateTeams)
  
  // Fonctionnalités de profil (uniquement celles utilisées)
  const managesProfilePhotos = computed(() => !!jetstream.value.managesProfilePhotos)
  const canUpdateProfileInformation = computed(() => !!jetstream.value.canUpdateProfileInformation)
  const canUpdatePassword = computed(() => !!jetstream.value.canUpdatePassword)
  const canManageTwoFactorAuthentication = computed(() => !!jetstream.value.canManageTwoFactorAuthentication)
  
  // Fonctionnalités API (uniquement celles utilisées)
  const hasApiFeatures = computed(() => !!jetstream.value.hasApiFeatures)
  
  // Fonctionnalités de compte (uniquement celles utilisées)
  const hasAccountDeletionFeatures = computed(() => !!jetstream.value.hasAccountDeletionFeatures)
  
  return {
    hasTeamFeatures,
    canCreateTeams,
    managesProfilePhotos,
    canUpdateProfileInformation,
    canUpdatePassword,
    canManageTwoFactorAuthentication,
    hasApiFeatures,
    hasAccountDeletionFeatures,
  }
}
