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
        ViewBag.Categorias = await _db.Categorias.OrderBy(c => c.Ordem).ThenBy(c => c.Nome).ToListAsync();
        return View(itens);
    }

    [HttpPost]
    public async Task<IActionResult> Adicionar(string nome, int? categoriaId, string loja1, string url1, string loja2, string url2, string loja3, string url3)
    {
        if (string.IsNullOrWhiteSpace(nome)) return RedirectToAction("Index");

        var links = new List<LinkProduto>();
        AddLink(links, loja1, url1, "Link 1");
        AddLink(links, loja2, url2, "Link 2");
        AddLink(links, loja3, url3, "Link 3");

        var desejo = new Desejo
        {
            Nome = nome.Trim(),
            Links = links,
            DataAdicao = DateTime.UtcNow,
            CategoriaId = categoriaId,
        };

        _db.Desejos.Add(desejo);
        await _db.SaveChangesAsync();
        return RedirectToAction("Index");
    }

    private static void AddLink(List<LinkProduto> links, string? loja, string? url, string fallbackNome)
    {
        if (string.IsNullOrWhiteSpace(url)) return;
        if (!url.StartsWith("http")) url = "https://" + url;
        links.Add(new LinkProduto
        {
            Loja = string.IsNullOrWhiteSpace(loja) ? fallbackNome : loja.Trim(),
            Url = url
        });
    }

    [HttpPost]
    public async Task<IActionResult> AdicionarLink(int id, string loja, string url)
    {
        var item = await _db.Desejos.FindAsync(id);
        if (item == null || string.IsNullOrWhiteSpace(url)) return RedirectToAction("Index");

        if (!url.StartsWith("http")) url = "https://" + url;
        var links = item.Links;
        links.Add(new LinkProduto
        {
            Loja = string.IsNullOrWhiteSpace(loja) ? $"Link {links.Count + 1}" : loja.Trim(),
            Url = url
        });
        item.Links = links;
        await _db.SaveChangesAsync();
        return RedirectToAction("Index");
    }

    [HttpPost]
    public async Task<IActionResult> RemoverLink(int id, int linkIndex)
    {
        var item = await _db.Desejos.FindAsync(id);
        if (item != null)
        {
            var links = item.Links;
            if (linkIndex >= 0 && linkIndex < links.Count)
            {
                links.RemoveAt(linkIndex);
                item.Links = links;
                await _db.SaveChangesAsync();
            }
        }
        return RedirectToAction("Index");
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
