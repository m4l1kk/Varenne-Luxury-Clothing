// Mock data for the static Milestone 1 frontend.
// In Milestone 2+ this is replaced by calls to the backend REST API.
const CATEGORIES = [
  { id: 1, name: "Women", icon: "bi-person-standing-dress", toneA: "#5b4636", toneB: "#1a1410" },
  { id: 2, name: "Men", icon: "bi-person-standing", toneA: "#2f3640", toneB: "#0e1013" },
  { id: 3, name: "Accessories", icon: "bi-handbag", toneA: "#6b5a3e", toneB: "#1b160d" }
];

const PRODUCTS = [
  { id: 1, name: "Cashmere Wrap Coat", category: "Women", price: 2450, tag: "New", icon: "bi-person-standing-dress",
    colors: ["#c8b79e", "#1c1c1c"], sizes: ["XS", "S", "M", "L"], toneA: "#8a7660", toneB: "#2b241d",
    description: "Double-faced Mongolian cashmere, hand-finished seams and a softly structured shoulder." },
  { id: 2, name: "Silk Slip Dress", category: "Women", price: 1290, tag: "Bestseller", icon: "bi-stars",
    colors: ["#0e0e0e", "#6e1f2b"], sizes: ["XS", "S", "M"], toneA: "#4b2a2f", toneB: "#140b0c",
    description: "Bias-cut silk charmeuse with an adjustable cowl neckline." },
  { id: 3, name: "Tailored Wool Suit", category: "Men", price: 3100, tag: "", icon: "bi-person-standing",
    colors: ["#1f2933", "#3b3b3b"], sizes: ["46", "48", "50", "52"], toneA: "#36404a", toneB: "#0e1114",
    description: "Super 150s Italian wool, half-canvassed jacket with peak lapels." },
  { id: 4, name: "Merino Rollneck", category: "Men", price: 540, tag: "", icon: "bi-layers",
    colors: ["#e8e0d2", "#0e0e0e", "#5a4a3a"], sizes: ["S", "M", "L", "XL"], toneA: "#6d6152", toneB: "#1d1a16",
    description: "Extra-fine merino knitted in a dense, smooth gauge." },
  { id: 5, name: "Calfskin Tote", category: "Accessories", price: 1850, tag: "Limited", icon: "bi-handbag",
    colors: ["#5a3b25", "#0e0e0e"], sizes: ["One size"], toneA: "#7a5636", toneB: "#22160d",
    description: "Vegetable-tanned calfskin with a suede-lined interior and gold-tone hardware." },
  { id: 6, name: "Leather Chelsea Boot", category: "Men", price: 890, tag: "", icon: "bi-gem",
    colors: ["#0e0e0e", "#4a2f1f"], sizes: ["40", "41", "42", "43", "44"], toneA: "#3e2c22", toneB: "#110c09",
    description: "Goodyear-welted boot in polished box calf." },
  { id: 7, name: "Silk Twill Scarf", category: "Accessories", price: 420, tag: "New", icon: "bi-flower1",
    colors: ["#b8955a", "#1c3a5e"], sizes: ["One size"], toneA: "#9c7c45", toneB: "#2a2012",
    description: "90 x 90 cm silk twill, hand-rolled edges, archive print." },
  { id: 8, name: "Pleated Wide Trousers", category: "Women", price: 780, tag: "", icon: "bi-brightness-alt-high",
    colors: ["#e8e0d2", "#0e0e0e"], sizes: ["34", "36", "38", "40"], toneA: "#7d7366", toneB: "#24211d",
    description: "High-waisted wool crepe with front pleats and a fluid leg." },
  { id: 9, name: "Wool Fedora Hat", category: "Accessories", price: 320, tag: "", icon: "bi-gem",
    colors: ["#3b2f2a", "#0e0e0e"], sizes: ["S", "M", "L"], toneA: "#5a4b42", toneB: "#1c1613",
    description: "Felted wool with a grosgrain ribbon band and a structured brim." }
];

// Demo bag contents so the cart view is not empty on first load.
const DEMO_CART = [
  { productId: 2, size: "S", qty: 1 },
  { productId: 7, size: "One size", qty: 2 }
];

const DEMO_ORDERS = [
  { id: "MN-10482", customer: "Amina H.", date: "2026-10-12", total: 3740, status: "Shipped" },
  { id: "MN-10481", customer: "Luca R.", date: "2026-10-11", total: 540, status: "Processing" },
  { id: "MN-10480", customer: "Sara K.", date: "2026-10-10", total: 1850, status: "Delivered" },
  { id: "MN-10479", customer: "Emir D.", date: "2026-10-09", total: 3100, status: "Cancelled" },
  { id: "MN-10478", customer: "Lejla M.", date: "2026-10-08", total: 1710, status: "Delivered" }
];
