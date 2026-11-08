# Decisions Log

Single source of truth for decisions already made. Cursor agents must read this before starting
work and must not contradict it. To change a decision, add a NEW entry that supersedes the old one
(never silently edit history).

**Statuses:** Accepted | Proposed | Superseded | Open (awaiting decision)

**Entry template**
```
## D-XXX: Title
- Status:
- Date:
- Context: why this came up
- Decision: what we chose
- Consequences: what this enables, costs, or rules out
- Supersedes / Superseded by:
```

---

## Product decisions

## D-001: Not a social media clone; utility and trust first
- Status: Accepted
- Date: 2026-10-26
- Context: The market is saturated with feed-based platforms. Our value is solving real problems for a diaspora audience.
- Decision: Build a high-trust utility network. Prioritise structured Needs/Offers, events, and accumulated knowledge over feeds, media sharing, and vanity metrics. The Reject list in `docs/product-brief.md` is binding.
- Consequences: No algorithmic "For You" feed, public follower counts, Stories/Reels, or engagement-bait mechanics. Success is measured by problems solved, not time on site.

## D-002: WhatsApp and Facebook groups are the real competition; design for coexistence
- Status: Accepted
- Date: 2026-10-26
- Context: Target users already coordinate in WhatsApp and Facebook groups.
- Decision: Win on structured, searchable, accumulating knowledge and trust rails. Support shareable links, invite links, and email digests rather than assuming users abandon existing channels.
- Consequences: Public/shareable URLs and WhatsApp-friendly link previews are requirements. No walled-garden assumptions.

## D-003: Launch is not restricted to one city; concentrate launch effort via clusters
- Status: Accepted
- Date: 2026-10-26
- Context: The founder's friend network is spread across multiple cities and can drive engagement through personal ties.
- Decision: The product is not city-restricted. Launch activity is concentrated: friends act as "founding hosts" for their city/circle, and only a few clusters are actively seeded at a time.
- Consequences: City is a first-class discovery concept. Founding-host tooling (invite links, pre-seeding) matters. Density per cluster is tracked, not just total users.

## D-004: Communities are user-created data; seed communities are ordinary data
- Status: Accepted
- Date: 2026-10-26
- Context: Empty platforms fail (cold start), but hard-coding communities contradicts the model.
- Decision: No community logic is hard-coded. The team may create seed communities through seed scripts or admin tools as normal data.
- Consequences: Seed scripts must be idempotent and separate from migrations. Starter communities are owned by real founding hosts where possible.

## D-005: Web app first (mobile-first, responsive, PWA-friendly)
- Status: Accepted
- Date: 2026-10-26
- Context: Cheaper to build; links are shareable via WhatsApp; fits the coexistence strategy.
- Decision: Build a responsive web app first. Native apps are deferred.
- Consequences: Public deep links, fast mobile load, and installable-PWA readiness are priorities.

## D-006: Private, invite-only beta before public launch
- Status: Superseded
- Date: 2026-10-26
- Context: Need to test moderation, scam handling, and density with a friendly group before opening up.
- Decision: Initial release is invite-only.
- Consequences: Invite system and sign-up gating are MVP requirements.
- Supersedes / Superseded by: Superseded by D-024

## D-007: Content model and priority
- Status: Accepted
- Date: 2026-10-26
- Decision: Need/Offer posts are the highest priority. Other types: Resource/Guide, Event, Introduction, Discussion (secondary), Business update (Phase 2).
- Consequences: Build a shared post base first, then Need/Offer on top. Need/Offer requires category, coarse location, urgency, expiry, and a resolved state.

## D-008: Images allowed with purpose; no video in MVP
- Status: Accepted
- Date: 2026-10-26
- Context: Events, rentals, and business pages need images, but media dumping is rejected.
- Decision: Support limited images in context. No video in MVP. No standalone photo-sharing surface.
- Consequences: Image upload needs size/type limits, scanning, and EXIF/location stripping.

## D-009: Direct messaging and business pages deferred to Phase 2
- Status: Accepted
- Date: 2026-10-26
- Context: DMs carry heavy safety burden; business pages only matter at density.
- Decision: MVP contact happens in post threads or via an opt-in external contact method. DMs and business pages follow once real usage shows the need.
- Consequences: Reply threads must be good enough to carry MVP. Do not build DMs "just in case".

## D-010: No in-app payments or escrow
- Status: Accepted
- Date: 2026-10-26
- Context: Liability and fraud exposure.
- Decision: The platform never handles money between members.
- Consequences: Payment-risk warnings on relevant posts. Monetisation (later) is organiser/business tools and featured placement, never transaction handling.

## D-011: Reputation via badges and confirmed help, not public scores
- Status: Accepted
- Date: 2026-10-26
- Context: Scores and vouching chains are gameable and cliquey.
- Decision: Trust signals are verified email/phone, optional ID verification, tenure, and "helped me" confirmations from the person helped. Shown as badges.
- Consequences: No leaderboards or numeric public scores. Built after launch data exists.

## D-012: Business recommendations instead of star ratings
- Status: Accepted
- Date: 2026-10-26
- Context: Defamation and fake-review risk.
- Decision: Show "recommended by members, in which community" contexts. (Phase 2.)

## D-013: Success metrics
- Status: Accepted
- Date: 2026-10-26
- Decision: Optimise for: % of Needs receiving a response and time to first response; % of Needs resolved; offline meetups attended via events; 4-week retention within a community; scam incidents per 1,000 posts; resource reuse/search.
- Consequences: Instrument these from the start (privacy-respecting, first-party). Do not optimise for time on site.

## Trust, safety and privacy decisions

## D-014: Scam resistance is first-class; moderation ships before real users
- Status: Accepted
- Date: 2026-10-26
- Decision: Reporting, moderation queue, payment warnings, and extra friction on housing/jobs/immigration posts must exist before any real user is onboarded.
- Consequences: Build step 6 (reporting and moderation) precedes any beta invites.

## D-015: Special-category data and privacy by design
- Status: Accepted (pending legal review)
- Date: 2026-10-26
- Context: Ethnicity and religion are special-category data under UK GDPR.
- Decision: Heritage-related attributes are optional, separately consented, and never required. Location is coarse by default. Users choose their visible name. Consent is stored with timestamps and policy versions. Data export and deletion are designed in from the start.
- Consequences: Legal counsel to validate before public launch (UK GDPR, Online Safety Act duties, OISC immigration-advice rules, anti-discrimination in housing/jobs). Audience is 18+.

## Access and architecture decisions

## D-016: Build order (vertical slices)
- Status: Superseded by D-039
- Date: 2026-10-26
- Decision: 1) Foundations 2) Identity and access 3) Admin skeleton 4) Communities 5) Post framework + Need/Offer 6) Reporting and moderation 7) Search and home surface 8) Notifications 9) Events and RSVP 10) Intro and Discussion posts 11) Trust signals 12) Private beta launch.
- Consequences: Do not build later steps before their dependencies. Step 6 must complete before step 12.
- Supersedes / Superseded by: Superseded by D-039

## D-039: Build order — member and community surfaces before platform Admin
- Status: Accepted
- Date: 2026-11-08
- Context: Platform Admin early (old D-016 step 3 / D-019) delayed finishing the surfaces members and community admins actually use. Communities and Need/Offer were already pulled forward in practice (D-034, D-036).
- Decision: New order: 1) Foundations 2) Identity 3) Communities (+ community admin) 4) Posts: Need/Offer + threads + community post views 5) Home + discovery 6) Community-scoped safety (report UX + community mod actions; no platform Admin required yet) 7) Notifications 8) Events + RSVP 9) Intro + Discussion 10) Trust signals 11) Platform Admin skeleton + T&S queue 12) Private beta. D-014 still holds: real-user beta only after reports can be actioned (community-scoped first is enough to proceed; platform queue before open beta).
- Consequences: Supersedes D-016 numbering. Softens D-019’s “Admin early” consequence — platform back-office moves to step 11. Do not start platform Admin until member/community slices through step 6 are solid.
- Supersedes / Superseded by: Supersedes D-016; qualifies D-019 timing

## D-040: Community-scoped report queue before platform T&S
- Status: Accepted
- Date: 2026-11-08
- Context: D-036 stored pending reports with no action path. D-039 step 6 requires community admins to act before platform Admin exists.
- Decision: Community Owners/Moderators with `community.moderate` see pending reports in Settings → Moderation. Actions: dismiss (content stays) or remove (soft-hide post/reply via `hidden_at`; sibling pending reports on the same target close as removed). Reports are scoped by `community_id`. Hidden posts leave the community feed and show a removed page to members; moderators can still open them. Platform T&S queue remains D-039 step 11.
- Consequences: Satisfies D-014 for closed community testing without a platform Admin app. No bans, appeals, or cross-community escalation in this slice.

## D-041: Need/Offer discovery — related Offers + notify Offer authors
- Status: Accepted
- Date: 2026-11-08
- Context: Offers were browse-only; Needs did not surface matching Offers, and Offer authors were not told when a related Need appeared. Auto-matching into nested Needs under Offers was rejected as confusing.
- Decision: Needs keep **one** category. When creating or viewing a Need, show related open Offers in the same community (same category; prefer coarse area overlap). CTA is “Reply on this Offer”. Posting a Need also creates an in-app `related_need` notification for authors of matching Offers (deduped per need/author; exclude self). Notifications list at `/notifications` with unread badge in nav. Offers remain separate posts — people reply on the Offer thread; they do not nest a Need under it.
- Consequences: Ties Offer usefulness to Need flow without DMs. Full notification preferences/email remain later (D-039 step 7 hardening). Multi-category Offers / multi-area fields stay deferred.

## D-042: Need/Offer category selects + optional Offer location
- Status: Accepted
- Date: 2026-11-08
- Context: Category chip grids were crowded; Offers often are not place-tied (e.g. immigration advice); Offer urgency was noise. D-041 deferred multi-category Offers.
- Decision: Need uses a searchable single `UiSelect` for one category; keeps urgency and optional single area. Offer uses `UiMultiSelect` for up to **3** categories; **no urgency** (server defaults expiry to `normal`); location is optional — unchecked “specific place” stores no areas (“Anywhere”); when checked, up to **3** areas. Posts gain `category_keys` and `location_labels` JSON (backfilled); `category_key` / `location_label` remain the primary values. Related Offers match when Need category ∈ Offer categories; non-location Offers still match regardless of Need area.
- Consequences: Supersedes D-041’s “multi deferred” note for this data. Edit-post and notification prefs remain later.

## D-043: Post show UX + Need author “This helped”
- Status: Accepted
- Date: 2026-11-08
- Context: Authors replying on their own Need/Offer was noise; safety notices were shown to authors about their own posts; timestamps were missing; public reply upvotes would look like likes (rejected by product rules) while trust is meant to come from the person helped.
- Decision: Authors cannot reply on their own Need or Offer (UI + server). Safety notices hide for the author. Need/Offer show has a back control to the community and relative timestamps (`created_at_human`, e.g. “2 hours ago” / “Yesterday”). On **Needs only**, the Need author may mark one or more replies “This helped” (`helped_at`); helped replies sort above others with a calm badge. No public upvote scores. Offer reply “helped” stays out.
- Consequences: Aligns with D-006 “helped me” signals without crowd ranking. Full trust badge surface remains D-039 step 10.

## D-017: Three-layer access model
- Status: Accepted
- Date: 2026-10-26
- Decision: Platform roles (Member, Platform Moderator, Ops Admin, Super Admin); community roles scoped per community (Owner, Moderator, Member); account status (Active, Suspended, Banned, Pending verification) as a separate field, not a role.
- Consequences: A person can moderate one community and be a plain member in another. Moderator powers never cross community boundaries.

## D-018: Permission-based RBAC with roles as data
- Status: Accepted
- Date: 2026-10-26
- Decision: Code checks permissions, not role names. Roles are bundles of permissions stored as data. Authorisation enforced server-side, default deny.
- Consequences: New roles can be added without code rewrites. Central permission-check layer required.

## D-019: Separate admin back-office; audited; MFA for Super Admin
- Status: Accepted
- Date: 2026-10-26
- Decision: Admin runs as a separate app/subdomain with its own auth rules. All admin actions go to an immutable audit log. Super Admin requires MFA and no silent impersonation. Trust & Safety and Ops permissions are separable even if one person holds both initially.
- Consequences: Back-office is built early (step 3) because every later feature needs a moderation surface.

## D-020: Auth uses an established library/provider; no hand-rolled auth
- Status: Accepted (provider choice open, see O-003)
- Date: 2026-10-26
- Decision: Email + password or magic link, optional Google sign-in, mandatory email verification. Phone verification is a trust upgrade, not a gate.
- Consequences: Provider must support sessions, reset, verification, rate limiting, and MFA for admins.

## D-021: Configuration as data; jurisdiction-ready from day one
- Status: Accepted
- Date: 2026-10-26
- Decision: Post categories, countries, regions, guardrail copy per jurisdiction live in data/config. UK/Nigeria is the first configuration, not the architecture.
- Consequences: US/Canada expansion should be configuration plus legal review, not a rewrite.

## D-022: Workflow with Cursor
- Status: Accepted
- Date: 2026-10-26
- Decision: One step at a time. Agent proposes a plan and waits for approval. Each task defines Goal, In scope, Out of scope, Acceptance criteria. Agent reports what was built, what was left out, and proposes decisions-log entries.
- Consequences: Rules live in `.cursor/rules/`. This log is updated after every task that changes direction.

> D-023 is reserved for the tech stack decision (O-002).

## D-024: Open sign-up to the app; access is controlled per community
- Status: Accepted
- Date: 2026-10-28
- Context: Most people arrive via a personal WhatsApp invite, but people without an invite should still be able to find and join the platform. Trust is protected at the community level rather than by gating the whole app.
- Decision: Anyone (18+) can create an account without an invite. Email verification is mandatory before posting or joining communities (D-020) for every account, including people who arrive through an invite link. An invite never skips or shortcuts email verification. Each community's creator chooses how people get in. For communities that require a request, a member of that community with the approval permission (e.g. Owner or Moderator) must approve the request before the person becomes a member. Invites remain a growth and context tool: an invite link names who invited the person and which community, and takes them into that community's join flow after sign-up.
- Consequences: Sign-up is not gated by invites. Community access settings and a join-request queue (request, approve, decline) are part of the Communities step (D-016 step 4), checked through the central permission layer and scoped to that one community (D-017, D-018). Landing pages must work both with and without an invite. D-014 still applies: reporting and moderation must exist before any real user is onboarded.
- Supersedes / Superseded by: Supersedes D-006

## D-025: Invite links never skip email verification
- Status: Accepted
- Date: 2026-10-28
- Context: Most visitors will arrive from a personal WhatsApp invite. That familiarity must not be treated as a trust shortcut.
- Decision: Arriving through an invite link does not skip, delay, or weaken email verification. A person who used an invite still cannot post or join a community until their email is verified (D-020, D-024). The invite only names who invited them and which community, then takes them into that community's join flow after they have a verified account.
- Consequences: Identity (D-016 step 2) must enforce this server-side. Join requests and community membership writes must refuse unverified accounts. Landing and join copy must say this plainly so invitees are not surprised.
- Supersedes / Superseded by: Clarifies D-020 and D-024. Does not change them.

## D-026: Brand colour — dusk lagoon and quiet moss
- Status: Accepted
- Date: 2026-10-28
- Context: The first palette was warm clay. The product needs to feel calm, intimate and collaborative rather than earthy-brown or clinical-blue.
- Decision: Primary is dusk lagoon (warm teal-cyan). Buttons and emphasis use `lagoon-700` (`#1B5A61`) so white text stays readable. Secondary is quiet moss (soft emerald), reserved for trust and safety marks. Surfaces stay on warm ivory paper (`#F6F3EE`) so the teal does not feel cold.
- Consequences: Tokens live in `resources/css/app.css` as `lagoon` and `moss`. Do not reintroduce clay/olive, and do not use screaming cyan or hot accents.
- Supersedes / Superseded by: Surface colour superseded by D-027

## D-027: Cool off-white page surface
- Status: Accepted
- Date: 2026-10-28
- Context: Warm ivory read as brown and flattened the white cards, especially on a phone.
- Decision: The app surface is cool off-white (`paper` `#F5F7F7`). Cards, sheets and buttons stay white so content lifts off the page. Keep generous spacing, especially on mobile; do not pack trust lines, example posts and actions into one dense stack.
- Consequences: Theme colour, PWA background and WhatsApp preview card follow `paper`. Do not return to beige or brown page backgrounds.
- Supersedes / Superseded by: Supersedes the ivory surface in D-026. Lagoon and moss are unchanged.

---

## D-028: Auth library is Laravel Fortify
- Status: Accepted
- Date: 2026-10-31
- Context: O-003 left the auth provider open. Sign-up must not hand-roll password hashing or sessions.
- Decision: Accounts use Laravel Fortify on top of Laravel's session guard and hashed passwords. Fortify's own routes are not registered; this app owns `/join` and, later, sign-in and password reset.
- Consequences: Password rules stay off until production. Login and forgot-password use the same Fortify session and hashing layer, on app-owned routes (`/sign-in`, `/forgot-password`, `/reset-password`).
- Supersedes / Superseded by: Resolves O-003

## D-029: Email verification is a short-lived 6-digit code
- Status: Accepted
- Date: 2026-10-31
- Context: People should confirm their email without leaving the sign-up page.
- Decision: After sign-up, the app emails a 6-digit code that expires in 10 minutes. The person types or pastes it on the same flow. Five wrong tries burns the code. Resend is rate-limited. A link is not required.
- Consequences: Codes are stored as an HMAC with the app key, not in plain text. Unverified accounts cannot open home or join a community (D-025).

## D-030: Signup address lookup uses postcodes.io
- Status: Accepted
- Date: 2026-10-31
- Context: Sign-up collects a house number and postcode, and must show the person the place it resolves to.
- Decision: Look up the postcode with postcodes.io. Store the house number, postcode, and composed address privately. The place other people can see is the area and city, such as "Pennywell & South Hylton, Sunderland", or the city alone when there is no finer area. postcodes.io does not return a street name.
- Consequences: This lookup is the UK launch configuration. Exact address fields stay off Inertia props and off public profile data. D-031 adds the house-level check postcodes.io cannot do.

## D-031: A house number is confirmed with Ideal Postcodes
- Status: Accepted
- Date: 2026-10-31
- Context: postcodes.io can name the area and city, and cannot confirm that a house number exists or return the street.
- Decision: After postcodes.io resolves the postcode, Ideal Postcodes returns the premises at that postcode. Sign-up continues only when the house number, flat, or building name matches one of them. The full address is shown to the person confirming it, and stored privately. Other people still see only the area and city from postcodes.io.
- Consequences: When Ideal Postcodes answers, a house number that is not on its list is rejected. When that service does not answer, a matching open-map street is used if one exists. If the map has no numbered house, the private line still uses the street at the postcode point, with the house number the person entered. The area and city are used only when no street is there. Other people still see only the area and city. An optional phone number, when given, must be a UK mobile and is stored as +447….

## D-032: The running app stores data in MySQL
- Status: Accepted
- Date: 2026-10-31
- Context: Local development had been using a SQLite file. A MySQL database named naijasquare now exists for the app.
- Decision: The application connection is MySQL, database naijasquare. Schema changes still go through versioned migrations. Automated tests keep using SQLite in memory and do not write to this database.
- Consequences: `.env.example` documents the local MySQL settings. This does not close O-002; the rest of the stack is still open.

## D-033: The first signed-in screen is orientation, not a feed
- Status: Superseded by D-037
- Date: 2026-10-31
- Context: D-016 puts the home surface after posts and moderation. A person who has just verified their email still needs a first screen.
- Decision: That screen welcomes them by first name and city, offers three entry points (communities, a Need, local resources), and lists real communities that match their city and interests. Joining uses the same suggestion rules as sign-up. There is no feed, checklist, or progress bar.
- Consequences: This does not build Need posts, the resource library, or search. Those stay on their later steps. A community with no members is not shown, and activity is not invented. Ask for help and Explore local resources explain the action until those steps exist.
- Supersedes / Superseded by: Superseded by D-037

## D-037: Home is an intent-driven utility surface
- Status: Superseded by D-038
- Date: 2026-11-07
- Context: Need/Offer posting exists (D-036). The orientation-only Home (D-033) no longer matches how members should use the product day to day. Engagement ranking and a social feed remain rejected.
- Decision: `/home` is a single-column (phone) / main+rail (desktop) utility surface: greeting and city chip; optional first-week checklist (join two communities, ask or answer a Need, say what you can help with) that disappears when complete; primary actions that only link to real destinations; the member’s open Needs/Offers; “Needs you could help with” ranked by help-with category match, unanswered state, location, urgency, then recency, with category filter chips; suggested communities via the existing suggestion rules. Empty sections collapse. Optional `help_category_keys` + `help_note` on the user profile power helper matching. Events, activity snapshots, “answers worth keeping”, and moderator strips stay later. Communities index upgrades (pin, mute, health badges, recently visited) are a separate follow-up.
- Consequences: Supersedes D-033. No algorithmic For You feed. Meetup/Events CTAs are not shown until step 9. Checklist is completion-based, not a gamified progress bar.
- Supersedes / Superseded by: Superseded by D-038

## D-038: Home is a 60/40 utility + growth surface
- Status: Accepted
- Date: 2026-11-08
- Context: D-037 established intent-driven Home. Launch growth depends on friends bringing friends (D-003) and WhatsApp coexistence (D-002). The first-week checklist needed a clearer progress focus, and communities needed pin favourites without becoming a social feed.
- Decision: Desktop Home is ~60/40 (Needs/actions left; communities + invite right). Greeting includes Ask/Offer. First-week is one dismissible progress focus card (skills chips live inside it; complete or “Not now”). Remove the old “Start here” tile row. **Bring your people** is a lagoon-tinted personal invite card (WhatsApp + copy link; `/join?invited_by={uid}`; inviter attribution stored as `invited_by_user_id`; share endpoint rate-limited). Your communities show ≤3 with pin (`community_user.pinned_at`, pinned first). Near-you shows 1–2 honest suggestions or a start-first empty state. Monograms use a stable colour from the community name. No community-specific WhatsApp share in this slice; no engagement ranking.
- Consequences: Supersedes D-037 layout specifics. Personal platform invites are attribution-only (not a trust or permissions shortcut). Community-scoped invite share stays later.
- Supersedes / Superseded by: Supersedes D-037

## D-034: Community access modes and join notes
- Status: Accepted
- Date: 2026-11-01
- Context: D-024 fixed "request to join, approved by a community admin". O-012 still needed the full set of modes and whether a join request carries a note.
- Decision: Communities have three access modes: `open` (anyone verified may join), `request` (default; optional short note for admins; admins approve or decline), and `invite_only` (no open join; membership via invite paths). UI may say Creator/Admin; roles and permissions remain Owner/Moderator/Member (D-017, D-018). Community tags and guideline templates live in `config/community.php`. Admin invites use email plus a simple in-app notification row.
- Consequences: O-012 is resolved. D-016 step 4 is pulled forward ahead of the platform Admin skeleton (step 3); community-scoped permissions are enough for this slice. Health metrics, pins, digests, and the report queue are honest UI stubs until steps 5–8. Cover image scanning stays on O-007.

## D-035: Public discoverability for listed open/request communities
- Status: Accepted
- Date: 2026-11-01
- Context: Search engines cannot rank login-walled community pages. Diaspora members search for place- and topic-based groups (e.g. professionals in Leeds).
- Decision: Listed communities with access mode `open` or `request` that have at least one member are publicly crawlable: teaser page at `/communities/{slug}`, explore hub at `/explore`, place pages at `/explore/{place}`, tag pages at `/explore/tag/{tag}`, plus `/sitemap.xml` and robots allowances. Invite-only and unlisted communities stay private (404 to guests, omitted from the sitemap). Public pages expose teaser fields only (name, location, description, tags, member count, cover, join CTA)—not member lists, posts, or admin tools. Slug URLs remain the public identifier; no separate public numeric ID.
- Consequences: Landing and marketing can deep-link into explore and community teasers. Join and posting still require a verified account (D-020, D-024, D-025). Full Inertia SSR is deferred; public pages use Blade meta via `withViewData` like the landing page.

## D-036: Need/Offer posting in communities
- Status: Accepted
- Date: 2026-11-01
- Context: D-016 step 5; members need a way to ask for and offer help inside a community without DMs (D-009).
- Decision: First post types are Need and Offer only. Categories, urgencies, default expiry windows, and report reasons live in `config/posts.php`. Creating a Need/Offer requires verified membership, accepted guidelines when required, `post.create`, and the community `who_can_post` setting (`members` or `admins`). All members with `post.reply` may reply in the thread. Authors can mark their own Need/Offer resolved. Reports create pending `reports` rows (no full moderation queue UI yet). Housing, jobs, and immigration categories show a payment-risk safety notice. `who_can_post` is set at community create and editable in settings.
- Consequences: Discussion/Event/Intro posts stay later (D-016). Image uploads stay on O-007. Full Trust & Safety queue remains step 6.

---

## Open decisions (resolve before the relevant build step)

| ID | Question | Needed by | Notes |
|---|---|---|---|
| O-001 | Product name and domain | Before beta | Working name only for now |
| O-002 | Tech stack (frontend, backend, database, ORM, testing) | Step 1 | App data is MySQL (D-032). Record the rest as D-023 once chosen; then update `.cursor/rules/40-code-quality.mdc` |
| O-003 | Auth provider/library | Step 2 | Resolved by D-028 (Laravel Fortify) |
| O-004 | Hosting and data region | Step 1 | Prefer UK/EU region for UK GDPR simplicity |
| O-005 | Email delivery provider | Step 2 | Verification, notifications, digests |
| O-006 | Search approach (DB full-text vs dedicated search) | Step 7 | Start simple unless scale demands more |
| O-007 | File/image storage and scanning | Step 5 | Needed for Need/Offer and Events images |
| O-008 | Legal review scope and timing | Before beta | UK GDPR, Online Safety Act, OISC, discrimination law |
| O-009 | Founding-host programme details | Before beta | Who, which clusters, what pre-seeded content |
| O-010 | Moderation staffing plan | Before beta | Who handles reports and how fast |
| O-011 | Analytics approach (first-party, privacy-respecting) | Step 8 | Needed for D-013 metrics |
| O-012 | Community access modes | Step 4 | Resolved by D-034 (`open` / `request` / `invite_only`; optional join note) |
