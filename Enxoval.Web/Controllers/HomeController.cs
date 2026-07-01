using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Enxoval.Web.Data;
using Enxoval.Web.Models;

namespace Enxoval.Web.Controllers;

public class HomeController : Controller
{
    private readonly AppDbContext _db;
    public HomeController(AppDbContext db) => _db = db;

    public async Task<IActionResult> Index()
    {
        var itens = await _db.Desejos.Include(d => d.Categoria).OrderByDescending(d => d.DataAdicao).ToListAsync();
        foreach (var item in itens)
        {
            item.Links = item.Links.OrderBy(l => l.Preco ?? decimal.MaxValue).ToList();
        }
        ViewBag.Categorias = await _db.Categorias.OrderBy(c => c.Ordem).ThenBy(c => c.Nome).ToListAsync();
        return View(itens);
    }

    [HttpPost]
    public async Task<IActionResult> Adicionar(string nome, int? categoriaId, string loja1, string url1, string preco1, string loja2, string url2, string preco2, string loja3, string url3, string preco3)
    {
        if (string.IsNullOrWhiteSpace(nome)) return RedirectToAction("Index");

        var links = new List<LinkProduto>();
        AddLink(links, loja1, url1, preco1, "Link 1");
        AddLink(links, loja2, url2, preco2, "Link 2");
        AddLink(links, loja3, url3, preco3, "Link 3");

        var desejo = new Desejo
        {
            Nome = nome.Trim(),
            Links = links.OrderBy(l => l.Preco ?? decimal.MaxValue).ToList(),
            DataAdicao = DateTime.UtcNow,
            CategoriaId = categoriaId
        };

        _db.Desejos.Add(desejo);
        await _db.SaveChangesAsync();
        return RedirectToAction("Index");
    }

    private static void AddLink(List<LinkProduto> links, string? loja, string? url, string? preco, string fallbackNome)
    {
        if (string.IsNullOrWhiteSpace(url)) return;
        if (!url.StartsWith("http")) url = "https://" + url;
        links.Add(new LinkProduto
        {
            Loja = string.IsNullOrWhiteSpace(loja) ? fallbackNome : loja.Trim(),
            Url = url,
            Preco = ParsePreco(preco)
        });
    }

    private static decimal? ParsePreco(string? valor)
    {
        if (string.IsNullOrWhiteSpace(valor)) return null;
        valor = valor.Trim().Replace("R$", "").Replace(" ", "");
        valor = valor.Replace(".", "").Replace(",", ".");
        if (decimal.TryParse(valor, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var result))
            return Math.Round(result, 2);
        return null;
    }

    [HttpPost]
    public async Task<IActionResult> Remover(int id)
    {
        var item = await _db.Desejos.FindAsync(id);
        if (item != null) { _db.Desejos.Remove(item); await _db.SaveChangesAsync(); }
        return RedirectToAction("Index");
    }

    [HttpPost]
    public async Task<IActionResult> ToggleComprado(int id)
    {
        var item = await _db.Desejos.FindAsync(id);
        if (item != null)
        {
            item.Comprado = !item.Comprado;
            await _db.SaveChangesAsync();
        }
        return RedirectToAction("Index");
    }
}
